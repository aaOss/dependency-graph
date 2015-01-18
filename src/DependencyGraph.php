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

namespace AlmostAnything\DependencyGraph;

/**
 * Description of DependencyGraph
 *
 * @author Rhys
 */
class DependencyGraph {

    protected $nodes = [];

    public function __construct() {
        
    }

    public function addNode(DependencyGraphNode $node) {
        $hash = spl_object_hash($node);
        if (!array_key_exists($hash, $this->nodes)) {
            $this->nodes[$hash] = $node;
        }
    }

    public function removeNode(DependencyGraphNode $node) {
        $hash = spl_object_hash($node);
        if (array_key_exists($hash, $this->nodes)) {
            unset($this->nodes[$hash]);
        }
    }
    
    public function merge(DependencyGraph $graph) {
        foreach ($graph->getRoots() as $root) {
            foreach ($root->getDescendents() as $node) {
                $this->addNode($node);
                $node->setGraph($this);
            }
        }
        
        return $this;
    }
    
    /**
     * Return the root nodes of the graph.
     * 
     * @return array
     */
    public function getRoots() {
        $roots = [];

        foreach ($this->nodes as $node) {
            if (!$node->isRoot()) {
                $roots [] = $node;
            }
        }

        return $roots;
    }

    public function getLeaves() {
        $roots = [];

        foreach ($this->nodes as $node) {
            if (!$node->isLeaf()) {
                $roots [] = $node;
            }
        }

        return $roots;
    }

}