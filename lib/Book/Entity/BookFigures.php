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
 * @ORM\Entity(repositoryClass="Book_Entity_Repository_BookFigures")
 * @ORM\Table(name="book_figs")
 */
class Book_Entity_BookFigures extends Zikula_EntityAccess {

    /**
     * sid field (record sid)
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $fid;
    
    /**
     * @ORM\Column(type="integer", length=20)
     */
    private $fig_number;
    
    /**
     * @ORM\Column(type="integer", length=20)
     */
    private $number;
    
    /**
     * @ORM\Column(type="integer", length=20)
     */
    private $bid;

    /**
     * Book img link
     * 
     * @ORM\Column(type="text")
     */
    private $img_link = '';

    /**
     * Book Figure Title
     * 
     * @ORM\Column(type="text")
     */
    private $title = '';

    
    /**
     * @ORM\Column(type="integer", length=4)
     */
    private $perm;
    
    /**
     * Book contents
     * 
     * @ORM\Column(type="text")
     */
    private $content;

    
    /**
     * Constructor 
     */
    public function __construct() {

        $this->title = '';
        $this->fig_number = 0;
        $this->number = 0;
        $this->bid = 0;
        $this->contents = '';
        $this->title = '';
        $this->img_link= '';
    }

    public function getFid() {
        return $this->aid;
    }

    public function setFid($fid) {
        $this->fid = $fid;
    }


    public function getFig_number() {
        return $this->fig_number;
    }

    public function setFig_number($fig_number) {
        $this->fig_number = $fig_number;
    }
    
    public function getChap_number() {
        return $this->number;
    }

    public function setChap_number($number) {
        $this->number = $number;
    }

    public function getBook_id() {
        return $this->bid;
    }

    public function setBook_id($bid) {
        $this->bid = $bid;
    }

    public function getImg_link() {
        return $this->img_link;
    }

    public function setImg_link($img_link) {
        $this->img_link = $img_link;
    }
    
    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function getPerm() {
        return $this->perm;
    }

    public function setPerm($perm) {
        $this->perm = $perm;
    }
    
    public function getContents() {
        return $this->contents;
    }

    public function setContents($contents) {
        $this->contents = $contents;
    }

}

?>
