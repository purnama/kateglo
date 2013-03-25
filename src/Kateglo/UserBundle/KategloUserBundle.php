<?php

namespace Kateglo\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class KategloUserBundle extends Bundle
{

    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
