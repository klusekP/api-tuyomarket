<?php


namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMSSerializer;


/**
 *
 * @MongoDB\Document()
 *
 * @JMSSerializer\ExclusionPolicy("all")
 */
class Shop
{
    /**
     * @MongoDB\Id
     * @JMSSerializer\Expose()
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotNull()
     * @JMSSerializer\Expose()
     *
     * @var string
     */
    protected $name;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotNull()
     * @JMSSerializer\Expose()
     *
     * @var string
     */
    protected $short;

    /**
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     *
     * @var string
     */
    protected $phone;

    /**
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     *
     * @var string
     */
    protected $email;

    /**
     *
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     *
     * @var string
     */
    protected $address;

    /**
     * @MongoDB\Field(type="hash")
     * @JMSSerializer\Expose()
     */
    protected $configurations;

    /**
     * @Assert\NotNull()
     *
     * @MongoDB\ReferenceOne(targetDocument="Client")
     * @JMSSerializer\Expose()
     */
    protected $client;

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
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * @param mixed $configurations
     */
    public function setConfigurations($configurations): void
    {
        $this->configurations = $configurations;
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
     * @return string
     */
    public function getShort(): ?string
    {
        return $this->short;
    }

    /**
     * @param string $short
     */
    public function setShort(?string $short): void
    {
        $this->short = $short;
    }

}