<?php


namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use FOS\UserBundle\Model\User as BaseUser;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Validator\Constraints as Assert;


/**
 *
 * @MongoDB\Document()
 *
 * @JMSSerializer\ExclusionPolicy("all")
 *
 */
class User extends BaseUser
{
    /**
     * @MongoDB\Id
     * @JMSSerializer\Expose()
     */
    protected $id;

    /**
     * @JMSSerializer\Expose()
     */
    protected $username;

    /**
     * @JMSSerializer\Expose()
     */
    protected $roles;

    /**
     * @JMSSerializer\Expose()
     * @JMSSerializer\SerializedName("email")
     */
    protected $emailCanonical;

    /**
     * @Assert\NotNull(groups={"client_link"})
     * @Assert\Valid(groups={"Client"})
     *
     * @MongoDB\ReferenceOne(targetDocument="Client", cascade="persist")
     * @JMSSerializer\Expose()
     *
     */
    protected $client;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Shop")
     * @JMSSerializer\Expose()
     */
    protected $shops;

    const ROLE_API = "ROLE_API",
        ROLE_CLIENT_ADMIN = "ROLE_CLIENT_ADMIN";

    public function __construct()
    {
        parent::__construct();

        $this->shops = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client): void
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param mixed $shops
     */
    public function setShops($shops): void
    {
        $this->shops = $shops;
    }

}