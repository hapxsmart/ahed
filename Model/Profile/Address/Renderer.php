<?php
namespace Aheadworks\Sarp2\Model\Profile\Address;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class Renderer
 * @package Aheadworks\Sarp2\Model\Profile\Address
 */
class Renderer
{
    /**
     * @var AddressConfig
     */
    private $addressConfig;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @param AddressConfig $addressConfig
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        AddressConfig $addressConfig,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->addressConfig = $addressConfig;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Render customer address
     *
     * @param  AddressInterface|ProfileAddressInterface $address
     * @param string $type
     * @return string
     */
    public function render($address, $type = 'html')
    {
        $formatType = $this->addressConfig->getFormatByCode($type);
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }

        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $formatType->getRenderer();
        $flatAddressArray = $this->convertAddressToArray($address);

        return empty($flatAddressArray) ? '' : $renderer->renderArray($flatAddressArray);
    }

    /**
     * Convert address to flat array
     *
     * @param AddressInterface|ProfileAddressInterface $address
     * @return array
     */
    private function convertAddressToArray($address)
    {
        if ($address instanceof ProfileAddressInterface) {
            $type = ProfileAddressInterface::class;
        } else {
            $type = AddressInterface::class;
        }
        $flatAddressArray = $this->extensibleDataObjectConverter->toFlatArray(
            $address,
            [],
            $type
        );

        return $this->prepareAddressData($flatAddressArray, $address);
    }

    /**
     * Prepare address data
     *
     * @param array $flatAddressArray
     * @param ProfileAddressInterface $address
     * @return mixed
     */
    private function prepareAddressData($flatAddressArray, $address)
    {
        $street = $address->getStreet();
        if (!empty($street) && is_array($street)) {
            // Unset flat street data
            $streetKeys = array_keys($street);
            foreach ($streetKeys as $key) {
                unset($flatAddressArray[$key]);
            }
            //Restore street as an array
            $flatAddressArray[AddressInterface::STREET] = $street;
        }

        return $flatAddressArray;
    }
}
