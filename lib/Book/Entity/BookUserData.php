<?php

/**
 * StrainID2
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 */
use Doctrine\ORM\Mapping as ORM;

/**
 * StrainID2 entity class
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity(repositoryClass="Book_Entity_Repository_BookUserData")
 * @ORM\Table(name="book_user_data")
 */
class Book_Entity_BookUserData extends Zikula_EntityAccess
{
    

    /**
     * cid field (record cid)
     *
     * @ORM\Id
     * @ORM\Column(type="integer", length=20)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $udid;
    
    /**
     * The number of the chapter
     * 
     * @ORM\Column(type="integer", length=20)
     */
    private $uid;
    
    /**
     * @ORM\Column(type="integer", length=20)
     */
    private $aid;
   
    /**
     * @ORM\Column(type="integer", length=20)
     */
    private $start;
    
    /**
     * @ORM\Column(type="integer", length=20)
     */
    private $end;
    
    
   
    /**
     * Constructor 
     */
    public function __construct()
    {
        $this->start = 0;
        $this->end = 0;
    }
    
    public function getUdid()
    {
        return $this->udid;
    }

    public function setUdid($udid)
    {
        $this->udid = $udid;
    }
    
    public function getUid()
    {
        return $this->uid;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
    }
    
    public function getArt_id()
    {
        return $this->aid;
    }

    public function setArt_id($aid)
    {
        $this->aid = $aid;
    }
    
    public function getStart()
    {
        return $this->start;
    }

    public function setStart($start)
    {
        $this->start = $start;
    }
    
    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd($end)
    {
        $this->end = $end;
    }
}
?>
