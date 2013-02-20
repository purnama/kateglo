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
namespace Momoku\Dao;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityNotFoundException;
use Momoku\Reflection\Annotation\AnnotationNotFoundException;
use Momoku\Reflection\Annotation\AnnotationValueNameNotFoundException;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
abstract class AbstractDao
{

    /**
     *
     * @var \Doctrine\ORM\EntityManager Entity Manager
     */
    protected $entityManager;

    /**
     * @var string Entity Id Property Names
     */
    protected $key;

    /**
     * @var string Entity Class Name
     */
    protected $entity;

    /**
     * @Inject
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param string $key
     * @param string $entity
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $objReflection = new \net\stubbles\lang\reflect\ReflectionObject($this);
        if (!$objReflection->hasAnnotation('KeyEntity')) {
            throw new AnnotationNotFoundException('KeyEntity');
        }
        $keyEntityAnnotation = $objReflection->getAnnotation('KeyEntity');
        if (!$keyEntityAnnotation->hasValueByName('key')) {
            throw new AnnotationValueNameNotFoundException('KeyEntity', 'key');
        }
        $this->key = $keyEntityAnnotation->getValueByName('key');
        if (!$keyEntityAnnotation->hasValueByName('entity')) {
            throw new AnnotationValueNameNotFoundException('KeyEntity', 'entity');
        }
        $this->entity = $keyEntityAnnotation->getValueByName('entity');
    }

    /**
     * @see \Doctrine\ORM\EntityManager::clear()
     */
    public function clear()
    {
        $this->entityManager->clear($this->entity);
    }

    /**
     * @see \Doctrine\ORM\EntityManager:contains()
     * @param object $entity
     * @return bool
     * @throws InvalidEntityException
     */
    public function contains($entity)
    {
        $this->validateEntity($entity);
        return $this->entityManager->contains($entity);
    }

    /**
     * Count all values represented by the specified where clause and pathExpression for the entity class this DAO persists.
     * This convenience method is meant for simple count queries. For complex ones, create custom ones in the product DAOs.
     *
     * @param string $where The where clause to append to the count query to narrow the counted values. Do not prefix with "where" as it is automatically added. The underlying query uses "e" as the identification variable, so the where will use "e", e.g. "e.name = 'Fred'". Specifying null or empty string disregards a where clause.
     * @param string $pathExpression A String representing the object graph navigation to the element to count. The underlying query uses "e" as the identification variable, so the pathExpression will use "e", e.g. "e.address.zip", "e.name", "e.id". Specify either an identification variable (e.g. "e") or a path expression (e.g. "e.address.zip"). Specifying null or empty string disregards a pathExpression (defaults to "*").
     * @return int
     */
    public function countAll($where = null, $pathExpression = null)
    {
        $whereClause = '';
        if ($where !== null && $where !== '') {
            $whereClause = 'WHERE ' . $where;
        }
        $pathExpressionClause = '*';
        if ($pathExpression !== null && $pathExpression !== '') {
            $pathExpressionClause = $pathExpression;
        }
        $query = $this->entityManager->createQuery('SELECT COUNT(' . $pathExpressionClause .
            ') FROM ' . $this->entity . ' e ' . $whereClause);
        return $query->getSingleResult();
    }

    /**
     * Find all entities, optionally starting in the resultset specified by startPosition and/or obtaining up to the number specified by maxResult.
     * This method is also useful for pagination of results.
     *
     * @param int $startPosition The starting position in the result set to retrieve.
     * @param int $maxResult The maximum number of rows to retrieve.
     * @return array
     */
    public function findAll($startPosition = null, $maxResult = null)
    {
        $query = $this->entityManager->createQuery('SELECT e FROM ' . $this->entity . ' e ');
        if ($startPosition !== null && $startPosition !== '') {
            $query->setFirstResult($startPosition);
        }
        if ($maxResult !== null && $maxResult !== '') {
            $query->setMaxResults($maxResult);
        }
        return $query->getResult();
    }

    /**
     * Find an entity by its id, optionally throwing an exception if not found (this prevents NullPointerExceptions from occurring later), and optionally locking the found entity.
     *
     * @param mixed $id The id (primary key) of the entity to find.
     * @param bool $exceptionIfNotFound Set to true if EntityNotFoundException need to be thrown instead of null
     * @param int $lockModeType Lock the entity in the persistence context for the specified lockMode
     * @param null $lockVersion version number of the Lock
     * @return object|null
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function findById($id, $exceptionIfNotFound = false, $lockModeType = LockMode::NONE, $lockVersion = null)
    {
        $result = $this->entityManager->find($this->entity, $id, $lockModeType, $lockVersion);
        if ($exceptionIfNotFound && $result === null) {
            throw new EntityNotFoundException;
        }
        return $result;
    }

    /**
     * @see \\Doctrine\ORM\EntityManager::flush()
     */
    public function flush()
    {
        $this->entityManager->flush();
    }

    /**
     * @see \\Doctrine\ORM\EntityManager::merge()
     * @param object $entity
     * @throws InvalidEntityException
     * @return object Entity
     */
    public function merge($entity)
    {
        $this->validateEntity($entity);
        return $this->entityManager->merge($entity);
    }

    /**
     * @see \Doctrine\ORM\EntityManager::persist()
     * @param object $entity
     * @throws InvalidEntityException
     */
    public function persist($entity)
    {
        $this->validateEntity($entity);
        $this->entityManager->persist($entity);
    }

    /**
     * @see \Doctrine\ORM\EntityManager::refresh()
     * @param object $entity
     * @throws InvalidEntityException
     */
    public function refresh($entity)
    {
        $this->validateEntity($entity);
        $this->entityManager->refresh($entity);
    }

    /**
     * @see \Doctrine\ORM\EntityManager::remove()
     * @param object $entity
     * @throws InvalidEntityException
     */
    public function remove($entity)
    {
        $this->validateEntity($entity);
        $this->entityManager->remove($entity);
    }

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Validating entity, compare with its expected type
     *
     * @param object $entity Entity to validate
     * @throws InvalidEntityException
     */
    protected function validateEntity($entity)
    {
        if (!is_object($entity) || !($entity instanceof $this->entity)) {
            throw new InvalidEntityException($entity, $this->entity);
        }
    }
}
