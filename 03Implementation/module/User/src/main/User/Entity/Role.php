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
namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 *
 * @Entity
 * @Table(name="role")
 */
class Role
{

    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     * @var int user id
     */
    protected $id;

    /**
     * @Version
     * @Column(type="integer")
     * @var int version
     */
    protected $version;

    /**
     * @Column(type="string", unique=true)
     * @var string name of the user
     */
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ManyToMany(targetEntity="\User\Entity\Role", mappedBy="children")
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ManyToMany(targetEntity="\User\Entity\Role", inversedBy="parent")
     * @JoinTable(name="rel_role",
     *      joinColumns={@JoinColumn(name="role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="child_id", referencedColumnName="id")}
     *      )
     */
    protected $children;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ManyToMany(targetEntity="\User\Entity\User", mappedBy="roles")
     */
    protected $users;

    public function __construct()
    {
        $this->parent = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getParent(){
        return $this->parent;
    }

    /**
     * @param \User\Entity\Role $role
     */
    public function addChildren(Role $role)
    {
        if (!$this->children->contains($role)) {
            $this->children->add($role);
            $role->addParent($this);
        }
    }

    /**
     * @param \User\Entity\Role $role
     */
    public function removeChildren(Role $role)
    {
        $removed = $this->children->removeElement($role);
        if ($removed !== null) {
            $removed->removeParent($this);
        }
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren(){
        return $this->children;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUsers(){
        return $this->users;
    }
}
