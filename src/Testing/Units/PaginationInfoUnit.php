<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

class PaginationInfoUnit extends ObjectUnit
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();

        $this->setUnits([
            'currentResults' => (new IntUnit()),
            'totalRecords' => new IntUnit(),
            'numberOfPages' => new IntUnit(),
            'itemsPerPage' => new IntUnit(),
            'currentPage' => new IntUnit(),
        ]);
    }
}
