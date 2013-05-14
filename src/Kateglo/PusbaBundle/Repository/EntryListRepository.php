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
namespace Kateglo\PusbaBundle\Repository;

use Doctrine\ORM\Query;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use Doctrine\ORM\EntityManager;
use Library\Repository\Repository;
use Library\Repository\Annotation\KeyEntity;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 * @KeyEntity(key="id", entity="Kateglo\PusbaBundle\Entity\PusbaEntryList")
 */
class EntryListRepository extends Repository
{

    /**
     * Reset List
     */
    public function reset()
    {
        $this->entityManager->createQuery(
            'UPDATE Kateglo\PusbaBundle\Entity\PusbaEntryList entryList
                SET entryList.found = false WHERE entryList.found = true'
        )->execute();
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getFoundEntry($offset = null, $limit = null)
    {
        $query = $this->entityManager->createQuery(
            "SELECT entryList FROM Kateglo\PusbaBundle\Entity\PusbaEntryList entryList
                WHERE entryList.found = true ORDER BY entryList.id"
        );

        if (!is_null($offset) && is_numeric($offset)) {
            $query->setFirstResult($offset);
        }
        if (!is_null($limit) && is_numeric($limit)) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }
}
