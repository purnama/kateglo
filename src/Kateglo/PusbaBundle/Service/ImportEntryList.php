<?php
/**
 *  Kateglo: Kamus, Tesaurus dan Glosarium bahasa Indonesia.
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
 * and is licensed under the GPL 2.0. For more information, see
 * <http://code.google.com/p/kateglo/>.
 *
 * @license <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html> GPL 2.0
 * @link    http://code.google.com/p/kateglo/
 * @copyright Copyright (c) 2009 Kateglo (http://code.google.com/p/kateglo/)
 */
namespace Kateglo\PusbaBundle\Service;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Service;
use Kateglo\PusbaBundle\Entity\EntryList;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 */
class ImportEntryList {

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     * @InjectParams({
     *  "entityManager" = @Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $entityManager){
        $this->entityManager = $entityManager;
    }

    /**
     * @param $content
     */
    public function import($content){
        @ini_set('memory_limit', '2048M');
        $entries = explode("\n", $content);
        foreach($entries as $entry){
            $entryList = new EntryList();
            $entryList->setEntry($entry);
            $this->entityManager->persist($entryList);
        }
        $this->entityManager->flush();
    }

}
