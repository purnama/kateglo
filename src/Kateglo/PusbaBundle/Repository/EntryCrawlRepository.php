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

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use JMS\DiExtraBundle\Annotation\Service;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Kateglo\PusbaBundle\Entity\EntryCrawl;
use Kateglo\PusbaBundle\Entity\EntryCrawlConfig;
use Kateglo\PusbaBundle\Entity\EntryCrawlHistory;
use Library\Repository\Repository;
use Library\Repository\Annotation\KeyEntity;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 * @KeyEntity(key="id", entity="Kateglo\PusbaBundle\Entity\EntryCrawl")
 */
class EntryCrawlRepository extends Repository
{

    /**
     * @return EntryCrawlConfig
     * @throws NonUniqueResultException If the query result is not unique.
     * @throws NoResultException If the query returned no result.
     */
    public function getCrawlConfig()
    {
        $query = $this->entityManager->createQuery(
            'SELECT crawlConfig FROM Kateglo\PusbaBundle\Entity\EntryCrawlConfig crawlConfig'
        );
        $query->setMaxResults(1);

        return $query->getSingleResult();
    }

    /**
     * @param string $entry
     * @return EntryCrawl
     * @throws NonUniqueResultException If the query result is not unique.
     * @throws NoResultException If the query returned no result.
     */
    public function findByEntry($entry)
    {
        $query = $this->entityManager->createQuery(
            "SELECT entryCrawl
                FROM Kateglo\PusbaBundle\Entity\EntryCrawl entryCrawl
                WHERE entryCrawl.entry = :entry"
        );
        $query->setParameter('entry', $entry);

        return $query->getSingleResult();
    }

    /**
     * @param EntryCrawlConfig $crawlConfig
     */
    public function persistConfig(EntryCrawlConfig $crawlConfig)
    {
        $this->entityManager->persist($crawlConfig);
    }

    /**
     * @param EntryCrawlHistory $crawlHistory
     */
    public function persistHistory(EntryCrawlHistory $crawlHistory)
    {
        $this->entityManager->persist($crawlHistory);
    }

}
