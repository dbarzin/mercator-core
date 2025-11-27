<?php

// app/Contracts/HasIcon.php

namespace Mercator\Core\Contracts;

interface HasIcon
{
    public function setIconId(?int $id): void;

    public function getIconId(): ?int;
}
