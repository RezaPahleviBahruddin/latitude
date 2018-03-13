<?php

namespace Latitude\QueryBuilder\Query;

use Latitude\QueryBuilder\TestCase;

use function Latitude\QueryBuilder\alias;
use function Latitude\QueryBuilder\field;
use function Latitude\QueryBuilder\fn;
use function Latitude\QueryBuilder\on;

class SelectTest extends TestCase
{
    public function testSelect()
    {
        $select = $this->engine
            ->select()
            ->from('users');

        $this->assertSql('SELECT * FROM users', $select);
        $this->assertParams([], $select);
    }

    public function testDistinct()
    {
        $select = $this->engine
            ->select()
            ->distinct();

        $this->assertSql('SELECT DISTINCT *', $select);
        $this->assertParams([], $select);
    }

    public function testColumns()
    {
        $select = $this->engine
            ->select('id', 'username')
            ->from('users');

        $this->assertSql('SELECT id, username FROM users', $select);
        $this->assertParams([], $select);
    }

    public function testJoin()
    {
        $select = $this->engine
            ->select('u.username', 'r.role', 'c.country')
            ->from(alias('users', 'u'))
            ->join(alias('roles', 'r'), on('u.role_id', 'r.id'))
            ->join(alias('countries', 'c'), on('u.country_id', 'c.id'));

        $expected = implode(' ', [
            'SELECT u.username, r.role, c.country',
            'FROM users AS u',
            'JOIN roles AS r ON u.role_id = r.id',
            'JOIN countries AS c ON u.country_id = c.id',
        ]);

        $this->assertSql($expected, $select);
        $this->assertParams([], $select);
    }

    public function testWhere()
    {
        $select = $this->engine
            ->select()
            ->from('users')
            ->where(field('id')->eq(1));

        $this->assertSql('SELECT * FROM users WHERE id = ?', $select);
        $this->assertParams([1], $select);
    }

    public function testWhereAnd()
    {
        $select = $this->engine
            ->select()
            ->from('users')
            ->andWhere(field('id')->eq(1))
            ->andWhere(field('username')->eq('admin'));

        $this->assertSql('SELECT * FROM users WHERE id = ? AND username = ?', $select);
        $this->assertParams([1, 'admin'], $select);
    }

    public function testWhereOr()
    {
        $select = $this->engine
            ->select()
            ->from('countries')
            ->orWhere(field('country')->eq('JP'))
            ->orWhere(field('country')->eq('CN'));

        $this->assertSql('SELECT * FROM countries WHERE country = ? OR country = ?', $select);
        $this->assertParams(['JP', 'CN'], $select);
    }

    public function testGroupBy()
    {
        $select = $this->engine
            ->select(
                alias(fn('COUNT', 'id'), 'total')
            )
            ->from('employees')
            ->groupBy('department');

        $expected = implode(' ', [
            'SELECT COUNT(id) AS total',
            'FROM employees',
            'GROUP BY department',
        ]);

        $this->assertSql($expected, $select);
        $this->assertParams([], $select);
    }

    public function testHaving()
    {
        $select = $this->engine
            ->select(
                'department',
                alias($sum = fn('SUM', 'salary'), 'total')
            )
            ->from('employees')
            ->groupBy('department')
            ->having(field($sum)->gt(5000));

        $expected = implode(' ', [
            'SELECT department, SUM(salary) AS total',
            'FROM employees',
            'GROUP BY department',
            'HAVING SUM(salary) > ?',
        ]);

        $this->assertSql($expected, $select);
        $this->assertParams([5000], $select);
    }

    public function testOrderBy()
    {
        $select = $this->engine
            ->select()
            ->from('users')
            ->orderBy('birthday');

        $this->assertSql('SELECT * FROM users ORDER BY birthday', $select);
        $this->assertParams([], $select);

    }

    public function testOrderByDirection()
    {
        $select = $this->engine
            ->select(
                'u.id',
                'u.username',
                alias(fn('COUNT', 'l.id'), 'total')
            )
            ->from(alias('users', 'u'))
            ->join(alias('logins', 'l'), on('u.id', 'l.user_id'))
            ->groupBy('l.user_id')
            ->orderBy('u.username')
            ->orderBy('total', 'desc');

        $expected = implode(' ', [
            'SELECT u.id, u.username, COUNT(l.id) AS total',
            'FROM users AS u',
            'JOIN logins AS l ON u.id = l.user_id',
            'GROUP BY l.user_id',
            'ORDER BY u.username, total DESC',
        ]);

        $this->assertSql($expected, $select);
        $this->assertParams([], $select);
    }
}
