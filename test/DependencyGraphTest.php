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

use AlmostAnything\DependencyGraph\DependencyGraphNode as Node;
use AlmostAnything\DependencyGraph\DependencyGraph as Graph;

/**
 * Description of DependencyGraphTest
 *
 * @author Rhys
 */
class DependencyGraphTest {

    public function testEverything() {

        /* creates a new graph object */
        $node = new Node('Dad');

        $node->child('Rhys') /* creates a child node with $node->graph, adds parent */
                ->child('Sqeak'); /* graph object will have Dad, Rhys & Sqeak */
        
        
        /* creates a new graph object with Mum */
        $mum = new Node('Mum');
        $mum->child('Seonaid');
        
        /* merges $mum->graph into $node->graph */
        $node->find('Rhys')->parent($mum);
        
        /* Graph now has Dad, Mom, Rhys, Seonaid & Squeak */
    }

}
