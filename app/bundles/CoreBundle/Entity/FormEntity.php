<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\UserBundle\Entity\User;

/**
 * Class FormEntity
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Serializer\ExclusionPolicy("all")
 */
class FormEntity extends CommonEntity
{

    /**
     * @ORM\Column(name="is_published", type="boolean")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"publishDetails"})
     */
    private $isPublished = true;

    /**
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"publishDetails"})
     */
    private $dateAdded = null;

    /**
     * @ORM\Column(name="created_by", type="integer", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"publishDetails"})
     */
    private $createdBy;

    /**
     * @ORM\Column(name="created_by_user", type="string", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"publishDetails"})
     */
    private $createdByUser;

    /**
     * @ORM\Column(name="date_modified", type="datetime", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"publishDetails"})
     */
    private $dateModified;

    /**
     * @ORM\Column(name="modified_by", type="integer", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"publishDetails"})
     */
    private $modifiedBy;

    /**
     * @ORM\Column(name="modified_by_user", type="string", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"publishDetails"})
     */
    private $modifiedByUser;

    /**
     * @ORM\Column(name="checked_out", type="datetime", nullable=true)
     */
    private $checkedOut;

    /**
     * @ORM\Column(name="checked_out_by", nullable=true, type="integer")
     */
    private $checkedOutBy;

    /**
     * @ORM\Column(name="checked_out_by_user", type="string", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"publishDetails"})
     */
    private $checkedOutByUser;

    /**
     * @var array
     */
    protected $changes = array();

    /**
     * Check publish status with option to check against category, publish up and down dates
     *
     * @param bool $checkPublishStatus
     * @param bool $checkCategoryStatus
     *
     * @return bool
     */
    public function isPublished($checkPublishStatus = true, $checkCategoryStatus = true)
    {
        if ($checkPublishStatus && method_exists($this, 'getPublishUp')) {
            $status = $this->getPublishStatus();
            if ($status == 'published') {
                //check to see if there is a category to check
                if ($checkCategoryStatus && method_exists($this, 'getCategory')) {
                    $category = $this->getCategory();
                    if ($category !== null && !$category->isPublished()) {
                        return false;
                    }
                }
            }

            return ($status == 'published') ? true : false;
        }

        return $this->getIsPublished();
    }

    /**
     * @param string $prop
     * @param mixed  $val
     */
    protected function isChanged($prop, $val)
    {
        $getter  = "get" . ucfirst($prop);
        $current = $this->$getter();
        if ($prop == 'category') {
            $currentId = ($current) ? $current->getId() : '';
            $newId     = ($val) ? $val->getId() : null;
            if ($currentId != $newId) {
                $this->changes[$prop] = array($currentId, $newId);
            }
        } elseif ($current != $val) {
            $this->changes[$prop] = array($current, $val);
        }
    }

    /**
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * Reset changes
     */
    public function resetChanges()
    {
        $this->changes = array();
    }

    /**
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     *
     * @return $this
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set dateModified
     *
     * @param \DateTime $dateModified
     *
     * @return $this
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Set checkedOut
     *
     * @param \DateTime $checkedOut
     *
     * @return $this
     */
    public function setCheckedOut($checkedOut)
    {
        $this->checkedOut = $checkedOut;

        return $this;
    }

    /**
     * Get checkedOut
     *
     * @return \DateTime
     */
    public function getCheckedOut()
    {
        return $this->checkedOut;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return $this
     */
    public function setCreatedBy($createdBy = null)
    {
        if ($createdBy != null && !$createdBy instanceof User ) {
            $this->createdBy = $createdBy;
        } else {
            $this->createdBy = ($createdBy != null) ? $createdBy->getId() : null;
            if ($createdBy != null) {
                $this->createdByUser = $createdBy->getName();
            }
        }

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set modifiedBy
     *
     * @param User $modifiedBy
     *
     * @return mixed
     */
    public function setModifiedBy($modifiedBy = null)
    {
        if ($modifiedBy != null && !$modifiedBy instanceof User ) {
            $this->modifiedBy = $modifiedBy;
        } else {
            $this->modifiedBy = ($modifiedBy != null) ? $modifiedBy->getId() : null;

            if ($modifiedBy != null) {
                $this->modifiedByUser = $modifiedBy->getName();
            }
        }

        return $this;
    }

    /**
     * Get modifiedBy
     *
     * @return User
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * Set checkedOutBy
     *
     * @param User $checkedOutBy
     *
     * @return mixed
     */
    public function setCheckedOutBy($checkedOutBy = null)
    {
        if ($checkedOutBy != null && !$checkedOutBy instanceof User ) {
            $this->checkedOutBy = $checkedOutBy;
        } else {
            $this->checkedOutBy = ($checkedOutBy != null) ? $checkedOutBy->getId() : null;

            if ($checkedOutBy != null) {
                $this->checkedOutByUser = $checkedOutBy->getName();
            }
        }

        return $this;
    }

    /**
     * Get checkedOutBy
     *
     * @return User
     */
    public function getCheckedOutBy()
    {
        return $this->checkedOutBy;
    }

    /**
     * Set isPublished
     *
     * @param boolean $isPublished
     *
     * @return $this
     */
    public function setIsPublished($isPublished)
    {
        $this->isChanged('isPublished', $isPublished);

        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Get isPublished
     *
     * @return boolean
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Check the publish status of an entity based on publish up and down datetimes
     *
     * @return string early|expired|published|unpublished
     * @throws \BadMethodCallException
     */
    public function getPublishStatus()
    {
        $dt      = new DateTimeHelper();
        $current = $dt->getLocalDateTime();
        if (!$this->isPublished(false)) {
            return 'unpublished';
        }

        $status = 'published';
        if (method_exists($this, 'getPublishUp')) {
            $up = $this->getPublishUp();
            if (!empty($up) && $current <= $up) {
                $status = 'pending';
            }
        }
        if (method_exists($this, 'getPublishDown')) {
            $down = $this->getPublishDown();
            if (!empty($down) && $current >= $down) {
                $status = 'expired';
            }
        }

        return $status;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        $id = $this->getId();

        return (empty($id)) ? true : false;
    }

    /**
     * @return string
     */
    public function getCheckedOutByUser()
    {
        return $this->checkedOutByUser;
    }

    /**
     * @return string
     */
    public function getCreatedByUser()
    {
        return $this->createdByUser;
    }

    /**
     * @return string
     */
    public function getModifiedByUser()
    {
        return $this->modifiedByUser;
    }
}
