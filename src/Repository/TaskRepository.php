<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Find all tasks for a user, optimized for display
     * @return Task[] Returns an array of Task objects
     */
    public function findUserTasksOptimized($user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.isDone', 'ASC')
            ->addOrderBy('t.dueDate', 'ASC')
            ->addOrderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
