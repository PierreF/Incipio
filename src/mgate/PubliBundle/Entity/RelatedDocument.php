<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\PubliBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use mgate\FormationBundle\Entity\Formation;
use mgate\PersonneBundle\Entity\Membre;
use mgate\PersonneBundle\Entity\Prospect;
use mgate\PubliBundle\Entity\Document;
use mgate\SuiviBundle\Entity\Etude;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class RelatedDocument
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Document", mappedBy="relation", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $document;

    /**
     * @ORM\ManyToOne(targetEntity="mgate\PersonneBundle\Entity\Membre", inversedBy="relatedDocuments", cascade={"persist"})
     * @ORM\JoinColumn(name="membre_id", referencedColumnName="id", nullable=true)
     */
    private $membre;

    /**
     * @ORM\ManyToOne(targetEntity="mgate\SuiviBundle\Entity\Etude", inversedBy="relatedDocuments", cascade={"persist"})
     * @ORM\JoinColumn(name="etude_id", referencedColumnName="id", nullable=true)
     */
    private $etude;

    /**
     * @ORM\ManyToOne(targetEntity="mgate\FormationBundle\Entity\Formation", cascade={"persist"})
     * @ORM\JoinColumn(name="formation_id", referencedColumnName="id", nullable=true)
     */
    private $formation;

    /**
     * @ORM\ManyToOne(targetEntity="mgate\PersonneBundle\Entity\Prospect", cascade={"persist"})
     * @ORM\JoinColumn(name="prospect_id", referencedColumnName="id", nullable=true)
     */
    private $prospect;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set document.
     *
     * @param Document $document
     *
     * @return CategorieDocument
     */
    public function setDocument(Document $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document.
     *
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set membre.
     *
     * @param Membre $membre
     *
     * @return CategorieDocument
     */
    public function setMembre(Membre $membre = null)
    {
        $this->membre = $membre;

        return $this;
    }

    /**
     * Get membre.
     *
     * @return Membre
     */
    public function getMembre()
    {
        return $this->membre;
    }

    /**
     * Set etude.
     *
     * @param Etude $etude
     *
     * @return CategorieDocument
     */
    public function setEtude(Etude $etude = null)
    {
        $this->etude = $etude;

        return $this;
    }

    /**
     * Get etude.
     *
     * @return Etude
     */
    public function getEtude()
    {
        return $this->etude;
    }

    /**
     * Set formation.
     *
     * @param Formation $formation
     *
     * @return CategorieDocument
     */
    public function setFormation(Formation $formation = null)
    {
        $this->formation = $formation;

        return $this;
    }

    /**
     * Get formation.
     *
     * @return Formation
     */
    public function getFormation()
    {
        return $this->formation;
    }

    /**
     * Set prospect.
     *
     * @param Prospect $prospect
     *
     * @return CategorieDocument
     */
    public function setProspect(Prospect $prospect = null)
    {
        $this->prospect = $prospect;

        return $this;
    }

    /**
     * Get prospect.
     *
     * @return Prospect
     */
    public function getProspect()
    {
        return $this->prospect;
    }

    public function __toString()
    {
        return 'RelatedDocument '.$this->getId();
    }
}
