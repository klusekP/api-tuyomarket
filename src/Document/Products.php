<?php


namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMSSerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @MongoDB\Document()
 *
 * @JMSSerializer\ExclusionPolicy("all")
 *
 */
class Products
{
    /**
     * @MongoDB\Id
     * @JMSSerializer\Expose()
     */
    protected $id;
    /**
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     * @JMSSerializer\SerializedName("product_name")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    protected $productName;
    /**
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     * @JMSSerializer\SerializedName("short_name")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    protected $shortName;
    /**
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     * @JMSSerializer\SerializedName("sku")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    protected $sku;
    /**
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     * @JMSSerializer\SerializedName("ean")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    protected $ean;
    /**
     * @MongoDB\Field(type="string")
     * @JMSSerializer\Expose()
     * @JMSSerializer\SerializedName("description")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    protected $description;

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     * @return Products
     */
    public function setProductName(string $productName): Products
    {
        $this->productName = $productName;
        return $this;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     * @return Products
     */
    public function setShortName($shortName): Products
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     * @return Products
     */
    public function setSku($sku): Products
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param string $ean
     * @return Products
     */
    public function setEan($ean): Products
    {
        $this->ean = $ean;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Products
     */
    public function setDescription($description): Products
    {
        $this->description = $description;
        return $this;
    }
}