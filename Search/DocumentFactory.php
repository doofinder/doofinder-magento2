<?php

namespace Doofinder\Feed\Search;

use Magento\Framework\Api\Search\DocumentFactory as SearchDocumentFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\Search\DocumentInterface;

/**
 * Class DocumentFactory
 * The class responsible for converting raw document to Magento Search Document
 */
class DocumentFactory
{
    /**
     * @var SearchDocumentFactory
     */
    private $documentFactory;

    /**
     * @var AttributeValueFactory
     */
    private $attributeValFactory;

    /**
     * @var EntityMetadata
     */
    private $entityMetadata;

    /**
     * DocumentFactory constructor.
     * @param SearchDocumentFactory $documentFactory
     * @param AttributeValueFactory $attributeValFactory
     * @param EntityMetadata $entityMetadata
     */
    public function __construct(
        SearchDocumentFactory $documentFactory,
        AttributeValueFactory $attributeValFactory,
        EntityMetadata $entityMetadata
    ) {
        $this->documentFactory = $documentFactory;
        $this->attributeValFactory = $attributeValFactory;
        $this->entityMetadata = $entityMetadata;
    }

    /**
     * @param array $rawDocument
     * @return DocumentInterface
     */
    public function create(array $rawDocument)
    {
        $attributes = [];
        $documentId = null;
        $entityId = $this->entityMetadata->getEntityId();

        foreach ($rawDocument as $fieldName => $value) {
            if ($fieldName === $entityId) {
                $documentId = $value;
            } elseif ($fieldName === '_score') {
                $attr = $this->attributeValFactory->create();
                $attr->setAttributeCode($fieldName);
                $attr->setValue($value);
                $attributes['score'] = $attr;
            }
        }

        $document = $this->documentFactory->create(['data' => [
            DocumentInterface::ID => $documentId,
            CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => $attributes,
        ]]);
        return $document;
    }
}
