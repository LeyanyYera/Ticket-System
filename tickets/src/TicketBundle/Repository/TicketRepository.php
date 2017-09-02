<?php

namespace TicketBundle\Repository;
use Doctrine\Common\Collections\Criteria;
use TicketBundle\Entity\to_char;

/**
 * TicketRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TicketRepository extends \Doctrine\ORM\EntityRepository
{
    public function getTickets($param){
        $exp = Criteria::expr();
        $criteria = Criteria::create()->where($exp->gte('ticket.id', 1));
        if(isset($param['title']) && !empty($param['title'])){
            $criteria->andWhere($exp->contains('ticket.title', $param['title']));
        }
        if(isset($param['status']) && !empty($param['status'])){
            $criteria->andWhere($exp->eq('status.id', $param['status']));
        }
        if(isset($param['email']) && !empty($param['email'])){
            $criteria->andWhere($exp->eq('assignee.email', $param['email']));
        }
        if(isset($param['author']) && !empty($param['author'])){
            $criteria->andWhere($exp->eq('assignee.id', $param['author']));
        }
        $qb = $this->_em->createQueryBuilder();
        $qb ->from('TicketBundle:Ticket', 'ticket')
            ->select ("assignee.name assignee_name,
                       author.name author_name,
                       status.name status_name,
                       status.id,
                       to_char(ticket.created, 'dd-mm-yyyy') created,
                       ticket.title,
                       ticket.body,
                       ticket.id")
            ->innerJoin('ticket.status', 'status')
            ->innerJoin('ticket.author', 'author')
            ->innerJoin('ticket.assignee', 'assignee')
            ->addCriteria($criteria);
        return $qb->getQuery()->getResult();
    }

    public function checkTicket($title, $body, $author){
        $exp = Criteria::expr();
        $criteria = Criteria::create()->where($exp->gte('ticket.id', 1));
        $criteria->andWhere($exp->eq('ticket.title', $title));
        $criteria->andWhere($exp->eq('ticket.body', $body));
        $criteria->andWhere($exp->eq('author.name', $author));

        $qb = $this->_em->createQueryBuilder();
        $qb ->from('TicketBundle:Ticket', 'ticket')
            ->select ('assignee.name assignee_name,
                       author.name author_name,
                       status.name status_name,
                       status.id,
                       ticket.created,
                       ticket.title,
                       ticket.body,
                       ticket.id')
            ->innerJoin('ticket.status', 'status')
            ->innerJoin('ticket.author', 'author')
            ->innerJoin('ticket.assignee', 'assignee')
            ->addCriteria($criteria);
        return $qb->getQuery()->getResult();
    }
}
