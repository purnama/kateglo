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
namespace Kateglo\PusbaBundle\Service\Kbbi;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use JMS\DiExtraBundle\Annotation\Service;
use Kateglo\PusbaBundle\Entity\EntryCrawl;
use Kateglo\PusbaBundle\Entity\EntryCrawlConfig;
use Kateglo\PusbaBundle\Entity\EntryCrawlHistory;
use Kateglo\PusbaBundle\Entity\EntryList;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Kateglo\PusbaBundle\Service\Kbbi\Exception\KbbiExtractorException;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 */
class Importer
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Requester
     */
    private $requester;

    /**
     * @param EntityManager $entityManager
     * @param Requester $requester
     * @InjectParams({
     *  "entityManager" = @Inject("doctrine.orm.entity_manager"),
     *  "requester" = @Inject("kateglo.pusba_bundle.service.kbbi.requester")
     * })
     */
    public function __construct(EntityManager $entityManager, Requester $requester)
    {
        $this->entityManager = $entityManager;
        $this->requester = $requester;
    }

    /**
     * @param $content
     */
    public function import($content)
    {
        @ini_set('memory_limit', '3069M');
        $entries = explode("\n", $content);
        foreach ($entries as $entry) {
            $entryList = new EntryList();
            $entryList->setEntry($entry);
            $this->entityManager->persist($entryList);
        }
        $this->entityManager->flush();
    }

    public function crawl($limit = 100, $start = false)
    {
        @ini_set('memory_limit', '3069M');
        $result = $this->entityManager->getRepository('Kateglo\PusbaBundle\Entity\EntryCrawlConfig')->findAll();
        /** @var $config EntryCrawlConfig */
        if (count($result) > 0) {
            $config = $result[0];
        } else {
            $config = new EntryCrawlConfig();
        }
        if ($start) {
            $config->setLastId(0);
            $this->entityManager->createQuery(
                'UPDATE Kateglo\PusbaBundle\Entity\EntryList entryList
                    SET entryList.found = false WHERE entryList.found = true'
            )->execute();
        }
        $query = $this->entityManager->createQuery(
            'SELECT entryList FROM Kateglo\PusbaBundle\Entity\EntryList entryList ORDER BY entryList.id'
        );
        $query->setFirstResult($config->getLastId());
        $query->setMaxResults($limit);
        $entries = $query->getResult();
        $this->requester->setOpCode(1);
        $crawlHistory = new EntryCrawlHistory();
        $crawlHistory->setStartId($config->getLastId());
        $crawlHistory->setStartTime(new \DateTime());
        try {
            /** @var $entry EntryList */
            foreach ($entries as $entry) {
                $this->requester->setParam($entry->getEntry());
                try {
                    $wordList = $this->requester->getRawExtracted();
                    foreach ($wordList as $word => $content) {
                        $crawlQuery = $this->entityManager->createQuery(
                            "SELECT entryCrawl
                                FROM Kateglo\PusbaBundle\Entity\EntryCrawl entryCrawl
                                WHERE entryCrawl.entry = :entry"
                        );
                        $crawlQuery->setParameter('entry', $word);
                        try {
                            $entryCrawl = $crawlQuery->getSingleResult();
                        } catch (NoResultException $e) {
                            $entryCrawl = new EntryCrawl();
                        }
                        $entryCrawl->setEntry($word);
                        $entryCrawl->setRaw($content);
                        $entryCrawl->setList($entry);
                        $entryCrawl->setLastUpdated(new \DateTime());
                        $this->entityManager->persist($entryCrawl);
                    }
                    $entry->setFound(true);
                    $this->entityManager->persist($entry);
                } catch (KbbiExtractorException $e) {
                    continue;
                }
            }
            if ($entry instanceof EntryList) {
                $config->setLastId($entry->getId());
                $config->setLastUpdated(new \DateTime());
            }
            $this->entityManager->persist($config);
            $crawlHistory->setFinishId($entry->getId());
            $crawlHistory->setFinishTime(new \DateTime());
            $crawlHistory->setStatus(true);
            $this->entityManager->persist($crawlHistory);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $crawlHistory->setFinishTime(new \DateTime());
            $crawlHistory->setStatus(false);
            $crawlHistory->setMessages($e->getMessage());
            $this->entityManager->persist($crawlHistory);
            $this->entityManager->flush($crawlHistory);
            throw $e;
        }
    }

}
