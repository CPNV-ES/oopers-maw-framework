<?php

namespace Tests\ORM;

use ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{

    private function getQueryBuilder()
    {
        return new QueryBuilder();
    }

    public function testSimpleSelect()
    {
        $q = $this->getQueryBuilder()
            ->from('users', 'u')
            ->select(['id', 'username', 'email'])
        ;
        $this->assertEquals('SELECT u.id, u.username, u.email FROM users u', $q->toSQL());
    }

    public function testWhere()
    {
        $q = $this->getQueryBuilder()
            ->from('users', 'u')
            ->select(['id', 'username', 'email'])
            ->where('u.email = "dimitri.rutz@eduvaud.ch"')
        ;
        $this->assertEquals("SELECT u.id, u.username, u.email FROM users u WHERE u.email = 'dimitri.rutz@eduvaud.ch'", $q->toSQL());
    }

    public function testMultipleWhere()
    {
        $q = $this->getQueryBuilder()
            ->from('users', 'u')
            ->select(['id', 'username', 'email'])
            ->where("u.email = 'dimitri.rutz@eduvaud.ch'")
            ->andWhere("u.username LIKE dimitri%")
        ;
        $this->assertEquals("SELECT u.id, u.username, u.email FROM users u WHERE u.email = 'dimitri.rutz@eduvaud.ch'", $q->toSQL());
    }

}
