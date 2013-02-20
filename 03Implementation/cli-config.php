<?php
/**
 *  Momoku Glue Stack Framework
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL 3.0. For more information, see
 * <http://github.com/purnama/momoku>.
 *
 * @license <http://www.gnu.org/copyleft/lesser.html> LGPL 3.0
 * @link    http://github.com/purnama/momoku
 * @copyright Copyright (c) 2013 Momoku (http://github.com/purnama/momoku)
 */


/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
// Setup autoloading
if (file_exists('vendor/autoload.php')) {
    $loader = include 'vendor/autoload.php';
}

if (isset($loader)) {
    $loader->add('Momoku', __DIR__.'/lib/main' );
    $loader->add('Demo', __DIR__.'/lib/main' );
}

$_SERVER['APPLICATION_ENV'] = 'development';

use net\stubbles\ioc\Binder;
use net\stubbles\ioc\binding\BindingScopes;
use Momoku\Ioc\Binding\BindingIndex;
use Momoku\Ioc\Binding\SessionBindingScope;

$binder = new Binder(new BindingIndex(new BindingScopes(null, new SessionBindingScope())));
$configuration = require 'config/application.config.php';
$binder->bindConstant('ApplicationConfiguration')->to($configuration);
foreach($configuration['providers'] as $impl => $providerClass){
    $binder->bind($impl)->toProviderClass($providerClass);
}

$entityManager = $binder->getInjector()->getInstance('Doctrine\ORM\EntityManager');

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($entityManager)
));