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
            ['value' => '*/5 * * * *', 'label' => 'Every 5 Minutes'],
            ['value' => '*/10 * * * *', 'label' => 'Every 10 Minutes'],
            ['value' => '*/15 * * * *', 'label' => 'Every 15 Minutes'],
            ['value' => '*/30 * * * *', 'label' => 'Every 30 Minutes'],
            ['value' => '0 * * * *', 'label' => 'Every 60 Minutes'],
        ];
    }
}
