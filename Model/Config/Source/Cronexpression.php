<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Cronexpression implements OptionSourceInterface
{

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => 'Everyday'],
            ['value' => '*/5 * * * *', 'label' => '5 Minutes'],
            ['value' => '*/10 * * * *', 'label' => '10 Minutes'],
            ['value' => '*/15 * * * *', 'label' => '15 Minutes'],
            ['value' => '*/30 * * * *', 'label' => '30 Minutes'],
            ['value' => '0 * * * *', 'label' => '60 Minutes'],
        ];
    }
}
