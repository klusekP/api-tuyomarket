<?php

use App\Document\ApiToken;
use App\Document\User;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Context;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

use GuzzleHttp\RequestOptions;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

require_once __DIR__ . '/../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';


/**
 * Defines application features from the specific context.
 */
class FeatureContext extends \Behat\MinkExtension\Context\RawMinkContext
{

    use \Behat\Symfony2Extension\Context\KernelDictionary;

    /**
     * The Guzzle HTTP Client.
     */
    protected $client;

    /**
     * The current resource
     */
    protected $resource;

    /**
     * The request payload
     * @var Behat\Gherkin\Node\PyStringNode
     */
    protected $requestPayload;

    /**
     * The user to use with HTTP basic authentication
     *
     * @var string
     */
    protected $authUser;

    /**
     * The password to use with HTTP basic authentication
     *
     * @var string
     */
    protected $authPassword;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    protected $headers = array();

    /**
     * The Guzzle HTTP Response.
     *
     * @var \GuzzleHttp\Psr7\Response
     */
    protected $response;

    /**
     * The last request that was used to make the response
     *
     * @var \GuzzleHttp\Psr7\Request
     */
    protected $lastRequest;

    /**
     * The decoded response object.
     */
    protected $responsePayload;

    /**
     * The current scope within the response payload
     * which conditions are asserted against.
     */
    protected $scope;

    /**
     * On HTML errors, if this is true, it prints out the h1/h2 to the console
     * to help debugging. It's assumed (like with Silex) that the most important
     * messages are sroted in h1/h2 tags. That might not be true for your
     * project, in which case you could set this to false. This isn't configurable
     * anywhere currently - just set this to false in the code or tweak
     * how the printing is done in printLastResponseOnError
     *
     * @var boolean
     */
    protected $useFancyExceptionReporting = true;

    /**
     * @var ConsoleOutput
     */
    protected $output;

    public function __construct($parameters, $kernel)
    {
        $config = isset($parameters['guzzle']) && is_array($parameters['guzzle']) ? $parameters['guzzle'] : array();

        $config['request.options'] = isset($config['request.options']) ? $config['request.options'] : array();

        $this->client = new Client($config);

        $this->dm = $kernel->getContainer()->get('doctrine_mongodb')->getManager();
    }

    /**
     * @BeforeScenario
     */
    public function clearData()
    {
        $collections = ['Shop', 'User', 'Client'];

        foreach($collections as $collection) {
            $this->dm->getDocumentCollection("App:" . $collection)->drop();
        }
    }

    /**
     * @Given /^I have the payload:$/
     */
    public function iHaveThePayload(PyStringNode $requestPayload)
    {
        $this->requestPayload = $requestPayload;
    }

    /**
     * @When /^I request "(GET|PUT|POST|DELETE|PATCH) ([^"]*)"$/
     */
    public function iRequest($httpMethod, $resource)
    {

        $resource = $this->processReplacements($resource);

        $this->resource = $resource;

        $this->responsePayload = null;

        $method = strtolower($httpMethod);

        try {
            $options = [];

            if (in_array($method, ["put", "post", "patch"])) {
                if ($this->requestPayload) {
                    $payload = $this->processReplacements($this->requestPayload->getRaw());
                    try {
                        assertJson($payload);
                        $options[RequestOptions::JSON] = json_decode($payload, true);
                    } catch (\Exception $e) {
                        $options[RequestOptions::BODY] = $payload;
                    }
                }
            }
            $options[RequestOptions::HEADERS] = $this->headers;

            $this->response = $this->client->$method($resource, $options);
        } catch (BadResponseException $e) {

            $response = $e->getResponse();

            // Sometimes the request will fail, at which point we have
            // no response at all. Let Guzzle give an error here, it's
            // pretty self-explanatory.
            if ($response === null) {
                throw $e;
            }

            $this->response = $e->getResponse();
        }
    }

    /**
     * @Given /^I authenticate with user "([^"]*)" and password "([^"]*)"$/
     */
    public function iAuthenticateWithEmailAndPassword($email, $password)
    {
        $this->authUser = $email;
        $this->authPassword = $password;
    }



    /**
     * @Given I authenticate with token for user :username
     */
    public function iAuthenticateWithTokenForUser($username)
    {
        $token = $this->getContainer()->get('lexik_jwt_authentication.encoder')->encode([
            "username" => $username,
            'exp' => time() + 3600
        ]);

        $this->headers["Authorization"] = "Bearer " . $token;
    }


    /**
     * @Given /^I set the "([^"]*)" header to be "([^"]*)"$/
     */
    public function iSetTheHeaderToBe($headerName, $value)
    {
        $this->headers[$headerName] = $value;
    }

    /**
     * @Then /^the response status code should be (?P<code>\d+)$/
     */
    public function iGetAResponse($statusCode)
    {
        $response = $this->response;

        $headerValueArray = $response->getHeader('Content-Type');

        $contentType = $headerValueArray[0];

        // looks for application/json or something like application/problem+json
        if (preg_match('#application\/(.)*\+?json#', $contentType)) {
            $bodyOutput = $response->getBody();
        } else {
            $bodyOutput = 'Output is "' . $contentType . '", which is not JSON and is therefore scary. Run the request manually.';
        }
        assertSame((int)$statusCode, (int)$this->getResponse()->getStatusCode(), $bodyOutput);
    }

    /**
     * @Given /^the "([^"]*)" header should be "([^"]*)"$/
     */
    public function theHeaderShouldBe($headerName, $expectedHeaderValue)
    {
        $response = $this->response;

        $headerValueArray = $response->getHeader($headerName);

        $contentType = $headerValueArray[0];

        assertEquals($expectedHeaderValue, $contentType);
    }

    /**
     * @Given /^the "([^"]*)" header should exist$/
     */
    public function theHeaderShouldExist($headerName)
    {
        $response = $this->getResponse();

        assertTrue($response->hasHeader($headerName));
    }

    /**
     * @Then /^the "([^"]*)" property should equal "([^"]*)"$/
     */
    public function thePropertyEquals($property, $expectedValue)
    {
        $payload = $this->getScopePayload();
        $actualValue = $this->arrayGet($payload, $property);

        assertEquals(
            $expectedValue,
            $actualValue,
            "Asserting the [$property] property in current scope equals [$expectedValue]: " . json_encode($payload)
        );
    }

    /**
     * @Then /^the "([^"]*)" property should contain "([^"]*)"$/
     */
    public function thePropertyShouldContain($property, $expectedValue)
    {
        $payload = $this->getScopePayload();
        $actualValue = $this->arrayGet($payload, $property);

        // if the property is actually an array, use JSON so we look in it deep
        $actualValue = is_array($actualValue) ? json_encode($actualValue, JSON_PRETTY_PRINT) : $actualValue;
        assertContains(
            $expectedValue,
            $actualValue,
            "Asserting the [$property] property in current scope contains [$expectedValue]: " . json_encode($payload)
        );
    }

    /**
     * @Given /^the "([^"]*)" property should not contain "([^"]*)"$/
     */
    public function thePropertyShouldNotContain($property, $expectedValue)
    {
        $payload = $this->getScopePayload();
        $actualValue = $this->arrayGet($payload, $property);

        // if the property is actually an array, use JSON so we look in it deep
        $actualValue = is_array($actualValue) ? json_encode($actualValue, JSON_PRETTY_PRINT) : $actualValue;
        assertNotContains(
            $expectedValue,
            $actualValue,
            "Asserting the [$property] property in current scope does not contain [$expectedValue]: " . json_encode($payload)
        );
    }

    /**
     * @Then /^the "([^"]*)" property should exist$/
     */
    public function thePropertyExists($property)
    {
        $payload = $this->getScopePayload();

        $message = sprintf(
            'Asserting the [%s] property exists in the scope [%s]: %s',
            $property,
            $this->scope,
            json_encode($payload)
        );

        assertTrue($this->arrayHas($payload, $property), $message);
    }

    /**
     * @Then /^the "([^"]*)" property should not exist$/
     */
    public function thePropertyDoesNotExist($property)
    {
        $payload = $this->getScopePayload();

        $message = sprintf(
            'Asserting the [%s] property does not exist in the scope [%s]: %s',
            $property,
            $this->scope,
            json_encode($payload)
        );

        assertFalse($this->arrayHas($payload, $property), $message);
    }

    /**
     * @Then /^the "([^"]*)" property should be an array$/
     */
    public function thePropertyIsAnArray($property)
    {
        $payload = $this->getScopePayload();

        $actualValue = $this->arrayGet($payload, $property);

        assertTrue(
            is_array($actualValue),
            "Asserting the [$property] property in current scope [{$this->scope}] is an array: " . json_encode($payload)
        );
    }

    /**
     * @Then /^the "([^"]*)" property should be an object$/
     */
    public function thePropertyIsAnObject($property)
    {
        $payload = $this->getScopePayload();

        $actualValue = $this->arrayGet($payload, $property);

        assertTrue(
            is_object($actualValue),
            "Asserting the [$property] property in current scope [{$this->scope}] is an object: " . json_encode($payload)
        );
    }

    /**
     * @Then /^the "([^"]*)" property should be an empty array$/
     */
    public function thePropertyIsAnEmptyArray($property)
    {
        $payload = $this->getScopePayload();
        $scopePayload = $this->arrayGet($payload, $property);

        assertTrue(
            is_array($scopePayload) and $scopePayload === array(),
            "Asserting the [$property] property in current scope [{$this->scope}] is an empty array: " . json_encode($payload)
        );
    }

    /**
     * @Then /^the "([^"]*)" property should contain (\d+) item(?:|s)$/
     */
    public function thePropertyContainsItems($property, $count)
    {
        $payload = $this->getScopePayload();

        assertCount(
            $count,
            $this->arrayGet($payload, $property),
            "Asserting the [$property] property contains [$count] items: " . json_encode($payload)
        );
    }

    /**
     * @Then /^the "([^"]*)" property should be an integer$/
     */
    public function thePropertyIsAnInteger($property)
    {
        $payload = $this->getScopePayload();

        isType(
            'int',
            $this->arrayGet($payload, $property),
            "Asserting the [$property] property in current scope [{$this->scope}] is an integer: " . json_encode($payload)
        );
    }

    /**
     * @Then /^the "([^"]*)" property should be a string$/
     */
    public function thePropertyIsAString($property)
    {
        $payload = $this->getScopePayload();

        isType(
            'string',
            $this->arrayGet($payload, $property, true),
            "Asserting the [$property] property in current scope [{$this->scope}] is a string: " . json_encode($payload)
        );
    }

    /**
     * @Then /^the "([^"]*)" property should be a string equalling "([^"]*)"$/
     */
    public function thePropertyIsAStringEqualling($property, $expectedValue)
    {
        $payload = $this->getScopePayload();

        $this->thePropertyIsAString($property);

        $actualValue = $this->arrayGet($payload, $property);

        assertSame(
            $actualValue,
            $expectedValue,
            "Asserting the [$property] property in current scope [{$this->scope}] is a string equalling [$expectedValue]."
        );
    }

    /**
     * @Then /^the "([^"]*)" property should be a boolean$/
     */
    public function thePropertyIsABoolean($property)
    {
        $payload = $this->getScopePayload();

        assertTrue(
            gettype($this->arrayGet($payload, $property)) == 'boolean',
            "Asserting the [$property] property in current scope [{$this->scope}] is a boolean."
        );
    }

    /**
     * @Then /^the "([^"]*)" property should be a boolean equalling "([^"]*)"$/
     */
    public function thePropertyIsABooleanEqualling($property, $expectedValue)
    {
        $payload = $this->getScopePayload();
        $actualValue = $this->arrayGet($payload, $property);

        if (!in_array($expectedValue, array('true', 'false'))) {
            throw new \InvalidArgumentException("Testing for booleans must be represented by [true] or [false].");
        }

        $this->thePropertyIsABoolean($property);

        assertSame(
            $actualValue,
            $expectedValue == 'true',
            "Asserting the [$property] property in current scope [{$this->scope}] is a boolean equalling [$expectedValue]."
        );
    }

    /**
     * @Then /^the "([^"]*)" property should be an integer equalling "([^"]*)"$/
     */
    public function thePropertyIsAIntegerEqualling($property, $expectedValue)
    {
        $payload = $this->getScopePayload();
        $actualValue = $this->arrayGet($payload, $property);

        $this->thePropertyIsAnInteger($property);

        assertSame(
            $actualValue,
            (int)$expectedValue,
            "Asserting the [$property] property in current scope [{$this->scope}] is an integer equalling [$expectedValue]."
        );
    }

    /**
     * @Then /^the "([^"]*)" property should be either:$/
     */
    public function thePropertyIsEither($property, PyStringNode $options)
    {
        $payload = $this->getScopePayload();
        $actualValue = $this->arrayGet($payload, $property);

        $valid = explode("\n", (string)$options);

        assertTrue(
            in_array($actualValue, $valid),
            sprintf(
                "Asserting the [%s] property in current scope [{$this->scope}] is in array of valid options [%s].",
                $property,
                implode(', ', $valid)
            )
        );
    }

    /**
     * @Then /^scope into the first "([^"]*)" property$/
     */
    public function scopeIntoTheFirstProperty($scope)
    {
        $this->scope = "{$scope}.0";
    }

    /**
     * @Then /^scope into the "([^"]*)" property$/
     */
    public function scopeIntoTheProperty($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @Then /^the following properties should exist:$/
     */
    public function thePropertiesExist(PyStringNode $propertiesString)
    {
        foreach (explode("\n", (string)$propertiesString) as $property) {
            $this->thePropertyExists($property);
        }
    }

    /**
     * @Given /^I follow the "([^"]*)" link$/
     */
    public function iFollowTheLink($linkName)
    {
        $payload = $this->getScopePayload();
        $href = $this->arrayGet($payload, sprintf('_links.%s.href', $linkName), true);

        // follow the link
        $this->iRequest('GET', $href);
    }

    /**
     * @Then /^reset scope$/
     */
    public function resetScope()
    {
        $this->scope = null;
    }

    /**
     * @Transform /^(\d+)$/
     */
    public function castStringToNumber($string)
    {
        return intval($string);
    }

    /**
     * @AfterScenario
     */
    public function printLastResponseOnError($scenarioEvent)
    {

//        $this->printDebug("Temporary stop");
//        return;
//        if ($scenarioEvent->getResult() == StepEvent::FAILED) {
//            if ($this->response) {
//                $body = $this->getResponse()->getBody(true);
//
//                // could we even ask them if they want to print out the error?
//                // or do it based on verbosity
//
//                // print some debug details
//                $this->printDebug('');
//                $this->printDebug('<error>Failure!</error> when making the following request:');
//                $this->printDebug(sprintf('<comment>%s</comment>: <info>%s</info>', $this->lastRequest->getMethod(), $this->lastRequest->getUrl()) . "\n");
//
//                if ($this->response->isContentType('application/json') || $this->response->isContentType('+json')) {
//                    $data = json_decode($body);
//                    if ($data === null) {
//                        // invalid JSON!
//                        $this->printDebug($body);
//                    } else {
//                        // valid JSON, print it pretty
//                        $this->printDebug(json_encode($data, JSON_PRETTY_PRINT));
//                    }
//                } else {
//                    // the response is HTML - see if we should print all of it or some of it
//                    $isValidHtml = strpos($body, '</body>') !== false;
//
//                    if ($this->useFancyExceptionReporting && $isValidHtml) {
//                        $this->printDebug('<error>Failure!</error> Below is a summary of the HTML response from the server.');
//
//                        // finds the h1 and h2 tags and prints them only
//                        $crawler = new Crawler($body);
//                        foreach ($crawler->filter('h1, h2')->extract(array('_text')) as $header) {
//                            $this->printDebug(sprintf('        ' . $header));
//                        }
//                    } else {
//                        $this->printDebug($body);
//                    }
//                }
//            }
//        }
    }

    /**
     * Checks the response exists and returns it.
     *
     * @return  \GuzzleHttp\Psr7\Response
     */
    protected function getResponse()
    {
        if (!$this->response) {
            throw new Exception("You must first make a request to check a response.");
        }

        return $this->response;
    }

    /**
     * Return the response payload from the current response.
     *
     * @return  mixed
     */
    protected function getResponsePayload()
    {
        if (!$this->responsePayload) {
            $json = json_decode($this->getResponse()->getBody(true), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $message = 'Failed to decode JSON body ';

                switch (json_last_error()) {
                    case JSON_ERROR_DEPTH:
                        $message .= '(Maximum stack depth exceeded).';
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $message .= '(Underflow or the modes mismatch).';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        $message .= '(Unexpected control character found).';
                        break;
                    case JSON_ERROR_SYNTAX:
                        $message .= '(Syntax error, malformed JSON): ' . "\n\n" . $this->getResponse()->getBody(true);
                        break;
                    case JSON_ERROR_UTF8:
                        $message .= '(Malformed UTF-8 characters, possibly incorrectly encoded).';
                        break;
                    default:
                        $message .= '(Unknown error).';
                        break;
                }

                throw new Exception($message);
            }

            $this->responsePayload = $json;
        }

        return $this->responsePayload;
    }

    /**
     * Returns the payload from the current scope within
     * the response.
     *
     * @return mixed
     */
    protected function getScopePayload()
    {
        $payload = $this->getResponsePayload();

        if (!$this->scope) {
            return $payload;
        }

        return $this->arrayGet($payload, $this->scope, true);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * Adapted further in this project
     *
     * @copyright   Taylor Otwell
     * @link        http://laravel.com/docs/helpers
     * @param       array $array
     * @param       string $key
     * @param bool $throwOnMissing
     * @param bool $checkForPresenceOnly If true, this function turns into arrayHas
     *                                    it just returns true/false if it exists
     * @return mixed
     * @throws Exception
     */
    protected function arrayGet($array, $key, $throwOnMissing = false, $checkForPresenceOnly = false)
    {
        // this seems like an odd case :/
        if (is_null($key)) {
            return $checkForPresenceOnly ? true : $array;
        }

        foreach (explode('.', $key) as $segment) {

            // check the passed array
            if ($segment == "_payload") {
                continue;
            }

            if (is_object($array)) {
                if (!property_exists($array, $segment)) {
                    if ($throwOnMissing) {
                        throw new \Exception(sprintf('Cannot find the key "%s"', $key));
                    }

                    // if we're checking for presence, return false - does not exist
                    return $checkForPresenceOnly ? false : null;
                }
                $array = $array->{$segment};

            } elseif (is_array($array)) {
                if (!array_key_exists($segment, $array)) {
                    if ($throwOnMissing) {
                        throw new \Exception(sprintf('Cannot find the key "%s"', $key));
                    }

                    // if we're checking for presence, return false - does not exist
                    return $checkForPresenceOnly ? false : null;
                }
                $array = $array[$segment];
            }
        }

        // if we're checking for presence, return true - *does* exist
        return $checkForPresenceOnly ? true : $array;
    }

    /**
     * Same as arrayGet (handles dot.operators), but just returns a boolean
     *
     * @param $array
     * @param $key
     * @return boolean
     */
    protected function arrayHas($array, $key)
    {
        return $this->arrayGet($array, $key, false, true);
    }

    /**
     * @Given /^print last response$/
     */
    public function printLastResponse()
    {
        if ($this->response) {
            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = clone ($this->response);
            $body = $response->getBody();
            $data = json_decode($body, true);

            if ($data) {
                $this->printDebug(json_encode($data, JSON_PRETTY_PRINT));
            }
        }
    }


    public function printDebug($string)
    {
        $this->getOutput()->writeln($string);
    }

    /**
     * @return ConsoleOutput
     */
    private function getOutput()
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
        }

        return $this->output;
    }

    /**
     * Evaluates expressions that are within % delimiters:
     *
     * Examples:
     *     %5+3%
     *
     *     %users.weaverryan.id%
     *
     * @param $payload
     * @throws Exception
     */
    private function processReplacements($payload)
    {
        $language = new ExpressionLanguage();

        $variables = [];

//        $variables = array(
//            'users' => new EntityLookup($this->getProjectHelper()->getUserRepository(), 'username'),
//            'projects' => new EntityLookup($this->getProjectHelper()->getProjectRepository(), 'name'),
//            'programmers' => new EntityLookup($this->getProjectHelper()->getProgrammerRepository(), 'nickname'),
//            'battles' => new EntityLookup($this->getProjectHelper()->getProgrammerRepository(), 'id'),
//        );

        while (false !== $startPos = strpos($payload, '%')) {
            $endPos = strpos($payload, '%', $startPos + 1);
            if (!$endPos) {
                throw new \Exception('Cannot find finishing % - expression look unbalanced!');
            }
            $expression = substr($payload, $startPos + 1, $endPos - $startPos - 1);

            // evaluate the expression
            try {
                $evaluated = $language->evaluate($expression, $variables);
            } catch (SyntaxError $e) {
                $this->printDebug('Error evaluating the following expression:');
                $this->printDebug($expression);

                throw $e;
            }
            // replace the expression with the final value
            $payload = str_replace('%' . $expression . '%', $evaluated, $payload);
        }

        return $payload;
    }

    /**
     * Asserts the the href of the given link name equals this value
     *
     * Since we're using HAL, this would look for something like:
     *      "_links.programmer.href": "/api/programmers/Fred"
     *
     * @Given /^the link "([^"]*)" should exist and its value should be "([^"]*)"$/
     */
    public function theLinkShouldExistAndItsValueShouldBe($linkName, $url)
    {
        $this->thePropertyEquals(
            sprintf('_links.%s.href', $linkName),
            $url
        );
    }

    /**
     * @Given /^the embedded "([^"]*)" should have a "([^"]*)" property equal to "([^"]*)"$/
     */
    public function theEmbeddedShouldHaveAPropertyEqualTo($embeddedName, $property, $value)
    {
        $this->thePropertyEquals(
            sprintf('_embedded.%s.%s', $embeddedName, $property),
            $value
        );
    }


    /**
     * @Given /^the user "([^"]*)" exists$/
     */
    public function theUserExists($username)
    {
        $this->thereIsAUserWithPassword($username, 'foo');
    }

    /**
     * @Given /^there is a user "([^"]*)" with password "([^"]*)"$/
     */
    public function thereIsAUserWithPassword($username, $password)
    {
        $this->createUser($username . '@foo.com', $password, $username);
    }

    /**
     * @Given the following shops exist:
     */
    public function theFollowingShopsExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {

            $id = $row['id'];
            unset($row['id']);

            $this->createShop($id, $row);
        }
    }

    /**
     * @Given the following users exist:
     */
    public function theFollowingUsersExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {

            $this->createUser($row['email'], $row['plainPassword'], $row['username'], $row['id']);
        }
    }

    /**
     * @Given the following clients exist:
     */
    public function theFollowingClientsExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {

            $id = $row['id'];
            unset($row['id']);

            $this->createClient($id, $row);
        }
    }
    public function createClient($id, $data)
    {

        $client = new \App\Document\Client();

        $client->setId($id);

        foreach ($data as $prop => $value) {

            $method = "set" . ucwords($prop);

            $client->$method($value);
        }

        $this->dm->persist($client);

        $this->dm->flush();

    }


    public function createShop($id, $data)
    {

        $shop = new \App\Document\Shop();
        $shop->setId($id);

        foreach ($data as $prop => $value) {

            $method = "set" . ucwords($prop);

            if ($prop == "configurations") {
                $value = json_decode($value, true);
            }

            $shop->$method($value);

        }

        $this->dm->persist($shop);

        $this->dm->flush();

    }

    public function createUser($email, $plainPassword, $username = null, $id = null)
    {
        $user = new User();

        if ($id) {
            $user->setId($id);
        }

        $user->setEmail($email);

        $user->setUsername($username ? $username : 'John' . rand(0, 10000));

        $user->setPlainPassword($plainPassword);

        $user->setEnabled(true);

        $user->addRole("ROLE_API");

        $this->dm->persist($user);

        $this->dm->flush();

        return $user;
    }

}
