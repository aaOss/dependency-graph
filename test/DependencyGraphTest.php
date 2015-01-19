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

    /**
     * Test that graph instances are handles correctly
     */
    public function testDisconectedGraph() {
        /* graph 1 */
        $node1 = new Node('node_1');
        $node2 = new Node('node_2');
        $node3 = new Node('node_3');
        
        $node1->addChild($node2);
        $node1->addChild($node3);
        $node2->addParent($node3);
        
        /* graph 2 */
        $node4 = new Node('node_4');
        $node5 = new Node('node_5');
        $node6 = new Node('node_6');
        
        $node4->addChild($node5);
        $node4->addChild($node6);
        $node5->addParent($node6);
        
        $graph1 = $node1->getGraph();
        $graph2 = $node4->getGraph();
        
        /* we have two distinct connected at this point */
        $this->assertNotEquals(spl_object_hash($graph1), spl_object_hash($graph2), 'Disconnected graph should not be equal');
        
        foreach ([$node2, $node3] as $node) {
            $this->assertEquals(spl_object_hash($graph1), spl_object_hash($node->getGraph()), 'Connected nodes should have same graph instance');
        }
        
        foreach ([$node5, $node6] as $node) {
            $this->assertEquals(spl_object_hash($graph2), spl_object_hash($node->getGraph()), 'Connected nodes should have same graph instance');
        }
        
        /* merge graphs */
        $graph1->merge($graph2);
        
        /* test that we now have one disconnected graph */
        foreach (range(1, 6) as $i) {
            $node = ${'node' . $i};
            $this->assertTrue($graph1->hasNode($node), 'Graph does not have node $node' . $i);
            $this->assertEquals(spl_object_hash($graph1), spl_object_hash($node->getGraph()), 'Nodes of disconnected graph should have same graph instance ($node' . $i . ')');
        }
    }
    
    public function testTopologicalSort() {
        $node1 = new Node('node_1');
        $node2 = new Node('node_2');
        $node3 = new Node('node_3');
        $node4 = new Node('node_4');
        $node5 = new Node('node_5');
        $node6 = new Node('node_6');
        
        $node1->addChild($node2);
        $node1->addChild($node3);
        $node2->addParent($node3);
        
        $node4->addChild($node5);
        $node4->addChild($node6);
        $node5->addParent($node6);
        
        $node3->addChild($node4);
        $node2->addChild($node6);
        
        $graph = $node1->getGraph();
        $list = $graph->topologicalSort();
        
        /* test that node does not occur before it's descendants in the resulting list */
        foreach ($list as $i => $node) {
            for($j = $i + 1; $j < count($list); $j++) {
                $this->assertFalse($node->hasAncestor($list[$j]));
            }
            for($j = $i - 1; $j >= 0; $j--) {
                $this->assertFalse($node->hasDescendant($list[$j]));
            }
        }
    }
            
    public function dont_testAssetManager() {
        $assets = new AssetManager();
        /* register javascript script files */
        $assets->addScript('jquery', '/web/assets/js/jquery.min.js')
                ->addScript('jquery-ui', '/web/assets/js/jquery-ui.min.js', ['jquery'])
                ->addScript('datatables', '/web/assets/js/datatables.js', ['jquery'])
                ->addScript('datatables-filter-plugin', '/web/assets/js/datatables.filter.js', ['datatables'])
                ->addScript('bootstrap', '/web/assets/js/bootstrap.js', ['jquery'])
                ->addScript('bootstrap-datepicker', '/web/assets/js/datepicker.js')
                // app js file
                ->addScript('my-app', '/web/assets/js/my-app.js', ['jquery', 'datatables', 'bootstrap-datepicker']);

        // ...

        /* use script files */
        $assets->useScript('my-app');

        // ...

//        $scripts = $assets->scripts();

        /* OUTPUT 
         * 
         * <script type="text/javascript" src="/web/assets/js/jquery.min.js">
         * <script type="text/javascript" src="/web/assets/js/bootstrap.js">
         * <script type="text/javascript" src="/web/assets/js/datatables.js">
         * <script type="text/javascript" src="/web/assets/js/bootstrap.js">
         * <script type="text/javascript" src="/web/assets/js/datepicker.js">
         * 
         */
    }

}
