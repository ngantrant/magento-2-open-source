<?php
/**
 * Converter of email templates configuration from \DOMDocument to array
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Email\Model\Template\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $result = [];
        /** @var \DOMNode $templateNode */
        foreach ($source->documentElement->childNodes as $templateNode) {
            if ($templateNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $templateId = $templateNode->attributes->getNamedItem('id')->nodeValue;
            $templateLabel = $templateNode->attributes->getNamedItem('label')->nodeValue;
            $templateFile = $templateNode->attributes->getNamedItem('file')->nodeValue;
            $templateType = $templateNode->attributes->getNamedItem('type')->nodeValue;
            $templateModule = $templateNode->attributes->getNamedItem('module')->nodeValue;
            $result[$templateId] = [
                'label' => $templateLabel,
                'file' => $templateFile,
                'type' => $templateType,
                'module' => $templateModule,
            ];
        }
        return $result;
    }
}
