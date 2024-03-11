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
            ['value' => '', 'label' => 'Disabled'],
            ['value' => '*/5 * * * *', 'label' => 'Each 5 Minutes'],
            ['value' => '*/15 * * * *', 'label' => 'Each 15 Minutes'],
            ['value' => '*/30 * * * *', 'label' => 'Each 30 Minutes'],
            ['value' => '0 */1 * * *', 'label' => 'Each hour'],
            ['value' => '0 */2 * * *', 'label' => 'Each 2 hours'],
            ['value' => '0 */6 * * *', 'label' => 'Each 6 hours'],
            ['value' => '0 */12 * * *', 'label' => 'Each 12 hours'],
            ['value' => '0 0 * * *', 'label' => 'Once a day'],
        ];
    }
}
