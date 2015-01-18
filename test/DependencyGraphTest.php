<?php

/*
 * The MIT License
 *
 * Copyright 2015 Rhys.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace AlmostAnything\DependencyGraphTest;

use AlmostAnything\DependencyGraph\CircularDependencyException;
use AlmostAnything\DependencyGraph\DependencyGraphNode as Node;
use PHPUnit_Framework_TestCase;

/**
 * Description of DependencyGraphTest
 *
 * @author Rhys
 */
class DependencyGraphTest extends PHPUnit_Framework_TestCase {

    public function testGraph() {
        $node1 = new Node('node_1');
        $node2 = new Node('node_2');
        $node3 = new Node('node_3');

        $this->assertEquals($node2->getValue(), $node1->addChild($node2)->getValue());
        $this->assertEquals($node3->getValue(), $node2->addParent($node3)->getValue());

        $node3->addParent($node1);

        /*
         *          $node1
         *           /  \
         *           |  $node3
         *           |    /
         *           $node2  
         */

        $this->assertTrue($node1->hasChild($node2));
        $this->assertTrue($node2->hasParent($node1));

        $this->assertTrue($node1->hasChild($node3));
        $this->assertTrue($node3->hasParent($node1));

        $this->assertTrue($node3->hasChild($node2));
        $this->assertTrue($node2->hasParent($node3));
        
        $this->assertTrue($node2->hasAncestor($node1));
        $this->assertTrue($node1->hasDescendant($node2));
    }

    /**
     * Test that circular dependencies are detected and prevented
     */
    public function testCircularDependency() {
        /*
         *          $node1
         *           /  \
         *           |  $node3
         *           |    /
         *           $node2  
         *             |
         *             x
         *             |
         *           $node1
         */

        $node1 = new Node('node_1');
        $node2 = new Node('node_2');
        $node3 = new Node('node_3');

        $node1->addChild($node2);
        $node1->addChild($node3);
        $node2->addParent($node3);

        $exThrown = false;
        
        try {
            $node2->addChild($node1);
        } catch (CircularDependencyException $ex) {
            $this->assertEquals($node2->getValue(), $ex->subject->getValue());
            $this->assertEquals($node1->getValue(), $ex->added->getValue());
            $this->assertEquals(CircularDependencyException::OPPERATION_ADD_CHILD, $ex->type);
        
            $exThrown = true;
        }

        $this->assertTrue($exThrown, 'Circular dependency was not detected');
    }

}
