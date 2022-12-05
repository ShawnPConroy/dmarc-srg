<?php

namespace Liuch\DmarcSrg;

use Liuch\DmarcSrg\Domains\Domain;
use Liuch\DmarcSrg\Domains\DomainList;
use Liuch\DmarcSrg\Database\DomainMapperInterface;

class DomainListTest extends \PHPUnit\Framework\TestCase
{
    public function testGettingList(): void
    {
        $data = [
            [ 'id' => 1, 'fqdn' => 'example.org' ],
            [ 'id' => 2, 'fqdn' => 'example.net' ],
            [ 'id' => 3, 'fqdn' => 'example.com' ]
        ];
        $result = (new DomainList($this->getDbMapperOnce('list', $data)))->getList();
        $this->assertSame(false, $result['more']);
        $list = $result['domains'];
        $this->assertSame(count($data), count($list));
        $this->assertContainsOnlyInstancesOf(Domain::class, $list);
        foreach ($list as $key => $dom) {
            $di = $data[$key];
            $this->assertSame($di['id'], $dom->id());
            $this->assertSame($di['fqdn'], $dom->fqdn());
        }
    }

    public function testGettingNames(): void
    {
        $names = [ 'example.org', 'example.net', 'example.com' ];
        $this->assertSame($names, (new DomainList($this->getDbMapperOnce('names', $names)))->names());
    }

    private function getDbMapperOnce(string $method, $value): object
    {
        $mapper = $this->getMockBuilder(DomainMapperInterface::class)
                       ->disableOriginalConstructor()
                       ->setMethods([ $method ])
                       ->getMockForAbstractClass();
        $mapper->expects($this->once())
               ->method($method)
               ->willReturnCallback(function () use ($value) {
                return $value;
               });

        $db = $this->getMockBuilder(\StdClass::class)
                   ->setMethods([ 'getMapper' ])
                   ->getMock();
        $db->method('getMapper')
           ->with('domain')
           ->willReturn($mapper);
        return $db;
    }
}
