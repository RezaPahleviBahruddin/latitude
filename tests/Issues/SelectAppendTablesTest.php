<?php

namespace Latitude\QueryBuilder\Issues;

use Latitude\QueryBuilder\TestCase;

/**
 * @link https://github.com/shadowhand/latitude/issues/58
 */
class SelectAppendTablesTest extends TestCase
{
    public function testSelectTable()
    {
        $query = $this->factory->select()->from('users');

        $this->assertSql('SELECT * FROM users', $query);
    }

    public function testSelectReplaceTables()
    {
        $query = $this->factory->select()->from('users')->from('posts');

        $this->assertSql('SELECT * FROM posts', $query);
    }

    public function testSelectAppendTables()
    {
        $query = $this->factory->select()->from('users')->addFrom('posts');

        $this->assertSql('SELECT * FROM users, posts', $query);
    }
}
