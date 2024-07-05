<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ImageResize implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => 'No Resize'],
            ['value' => 'df_small', 'label' => 'Magento Small Image: 370 x 370 px'],
            ['value' => 'df_thumbnail', 'label' => ' Magento Thumbnail Image: 100 x 100 px'],
            ['value' => 'df_base', 'label' => 'Magento Base Image: 470 × 470 px'],
            ['value' => 'df_swatch', 'label' => 'Magento Swatch Image: 50 x 50 px']
        ];
    }
}
