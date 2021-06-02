<?php


namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMSSerializer;


/**
 *
 * @MongoDB\Document()
 *
 * @JMSSerializer\ExclusionPolicy("all")
 */
class Client
{
    /**
     * @MongoDB\Id
     * @JMSSerializer\Expose()
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     * @JMSSerializer\Expose()
     *
     * @var string
     */
    protected $name;

    /**
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     *
     * @var string
     */
    protected $short;

    /**
     * @MongoDB\Field(type="string")
     * @MongoDB\UniqueIndex
     * @Assert\NotNull()
     * @JMSSerializer\Expose()
     *
     * @var string
     */
    protected $nip;

    /**
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     *
     * @var string
     */
    protected $regon;

    /**
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     *
     * @var string
     */
    protected $email;

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
    protected $address;

    /**
     * @MongoDB\ReferenceMany(targetDocument="Shop")
     * @JMSSerializer\Expose()
     *
     */
    protected $shops;


    public function __construct()
    {
        $this->shops = new ArrayCollection();
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
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
    public function setShort(string $short): void
    {
        $this->short = $short;
    }

    /**
     * @return string
     */
    public function getNip(): ?string
    {
        return $this->nip;
    }

    /**
     * @param string $nip
     */
    public function setNip(string $nip): void
    {
        $this->nip = $nip;
    }

    /**
     * @return string
     */
    public function getRegon(): ?string
    {
        return $this->regon;
    }

    /**
     * @param string $regon
     */
    public function setRegon(string $regon): void
    {
        $this->regon = $regon;
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
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
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
    public function setAddress(string $address): void
    {
        $this->address = $address;
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