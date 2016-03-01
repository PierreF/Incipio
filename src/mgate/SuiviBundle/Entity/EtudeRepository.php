<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\SuiviBundle\Entity;

use Doctrine\ORM\EntityRepository;
use n7consulting\RhBundle\Entity\Competence;

/**
 * EtudeRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EtudeRepository extends EntityRepository
{
    public function findByNumero($numero)
    {
        $mandat = (int) ($numero / 100);
        $num = $numero % 100;

        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('e')
                    ->from('mgateSuiviBundle:Etude', 'e')
                    ->where("e.mandat = $mandat")
                    ->andWhere("e.num = $num");

        return $query->getQuery()->getOneOrNullResult();
    }

    public function getEtudesCa()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('e')
                        ->from('mgateSuiviBundle:Cc', 'cc')
                        ->leftJoin('cc.etude', 'e');
                        //->addSelect('e')
                        //->where('e.cc IS NOT NULL')
                        //->addOrderBy('cc.dateSignature');


        return $query->getQuery()->getResult();
    }


    public function findByCompetence(Competence $competence){
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('e')
            ->from('mgateSuiviBundle:Etude', 'e')
            ->leftJoin('e.competences', 'c')
            ->addSelect('c');

        $query = $query->add('where', $query->expr()->in('c', ':c'))
            ->setParameter('c', $competence)
            ->getQuery();

        return $query->getResult();
    }
}
