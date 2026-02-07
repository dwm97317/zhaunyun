<?php
/**
 * PHPUnit 示例测试
 * 
 * 验证 PHPUnit 安装是否正常工作
 */

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * 基础断言测试
     */
    public function testBasicAssertion()
    {
        $this->assertTrue(true);
        $this->assertEquals(2, 1 + 1);
        $this->assertIsString('hello');
    }
    
    /**
     * 数组测试
     */
    public function testArrayOperations()
    {
        $array = ['a', 'b', 'c'];
        
        $this->assertCount(3, $array);
        $this->assertContains('b', $array);
        $this->assertNotContains('d', $array);
    }
    
    /**
     * 异常测试
     */
    public function testException()
    {
        $this->expectException(InvalidArgumentException::class);
        
        throw new InvalidArgumentException('测试异常');
    }
}
