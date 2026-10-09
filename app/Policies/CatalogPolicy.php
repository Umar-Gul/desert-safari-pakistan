<?php

declare(strict_types=1);

namespace App\Policies;

/** Content: tours, categories, FAQs, blog, gallery... Editors can write, support is read-only. */
class CatalogPolicy extends BasePolicy {}