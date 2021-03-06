<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\PersonneBundle\Entity;

use Doctrine\ORM\EntityRepository;
use n7consulting\RhBundle\Entity\Competence;

/**
 * MembreRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MembreRepository extends EntityRepository
{
    public function getIntervenantsParPromo()
    {
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('m')->from('mgatePersonneBundle:Membre', 'm')
            ->innerJoin('m.missions', 'mi')
            ->orderBy('m.promotion', 'ASC');

        return $query->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getMembresParPromo()
    {
        $entities = $this->findBy(array(), array('promotion' => 'desc'));
        $membresParMandat = array();
        foreach ($entities as $membre) {
            $promo = $membre->getPromotion();
            if (array_key_exists($promo, $membresParMandat)) {
                $membresParMandat[$promo][] = $membre;
            } else {
                $membresParMandat[$promo] = array($membre);
            }
        }

        return $membresParMandat;
    }

    public function getCotisants()
    {
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('m')->from('mgatePersonneBundle:Membre', 'm')
            ->innerJoin('m.mandats', 'ma')
            ->innerJoin('ma.poste', 'p')
            ->where('p.intitule LIKE :membre')
            ->andWhere('ma.finMandat > CURRENT_DATE()')
            ->setParameter('membre', 'Membre');

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne tous les membres connaissant $competence.
     *
     * @param Competence $competence
     *
     * @return array
     */
    public function findByCompetence(Competence $competence)
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('m')
            ->from('mgatePersonneBundle:Membre', 'm')
            ->leftJoin('m.competences', 'c')
            ->addSelect('c')
            ->where(':competence MEMBER OF m.competences')
            ->setParameter('competence', $competence)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Retourne un query builder de tous les membres ayant au moins un poste.
     * Utile dans le cas où l'on souhaite faire un formulaire d'uniquement les membres de la junior.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByMandatNonNulQueryBuilder()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb
            ->select('m')
            ->from('mgatePersonneBundle:Membre', 'm')
            ->leftJoin('m.mandats', 'mm')
            ->where('mm.id IS NOT NULL');

        return $query;
    }

    /** Fonction retournant une jointure entre un membre et ses competences */
    public function getMembreCompetences($id)
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('m')
            ->from('mgatePersonneBundle:Membre', 'm')
            ->where(' m.id = :id ')
            ->setParameter('id', $id)
            ->leftJoin('m.competences', 'c')
            ->addSelect('c')
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getByCompetencesNonNul()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb
            ->select('m')
            ->from('mgatePersonneBundle:Membre', 'm')
            ->leftJoin('m.competences', 'competences')
            ->where('competences.id IS NOT NULL')
            ->orderBy('m.id', 'asc')
            ->getQuery();

        return $query->getResult();
    }

    /** Fonction retournant l'ensemble des personnes ayant déjà fait une mission + les études associées aux missions
     * Utilisée notamment dans la liste des intervenants.
     * Permet de passer de 64 à 3 requetes sur la page de liste des intervenants.
     */
    public function getByMissionsNonNul()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb
            ->select('m')
            ->from('mgatePersonneBundle:Membre', 'm')
            ->leftJoin('m.missions', 'missions')
            ->addSelect('missions')
            ->leftJoin('m.personne', 'personne')
            ->addSelect('personne')
            ->leftJoin('missions.repartitionsJEH', 'repartitionsJEH')
            ->addSelect('repartitionsJEH')
            ->leftJoin('missions.etude', 'etude')
            ->addSelect('etude')
            ->leftJoin('etude.cc', 'cc')
            ->addSelect('cc')
            ->leftJoin('etude.ap', 'ap')
            ->addSelect('ap')
            ->where('missions.id IS NOT NULL')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Fonction retournant l'ensemble des membres avec une jointure sur les mandats et les postes.ti.
     *
     * @return array
     */
    public function getMembres()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb
            ->select('m')
            ->from('mgatePersonneBundle:Membre', 'm')
            ->leftJoin('m.mandats', 'mandats')
            ->addSelect('mandats')
            ->leftJoin('mandats.poste', 'poste')
            ->addSelect('poste')
            ->getQuery();

        return $query->getResult();
    }
}
