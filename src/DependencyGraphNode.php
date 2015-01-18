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
 * Dependency Graph data structure
 * 
 * @author Rhys
 */
class DependencyGraphNode {

    /** @var DependencyGraph */
    protected $graph;

    /** @var mixed */
    protected $value;

    /** @var array */
    protected $parents = [];

    /** @var array */
    protected $children = [];

    /**
     * Constructor
     * 
     * @param mixed $value
     * @param DependencyGraph $graph Register this node with an existing graph
     * @param array $parents
     * @param array $children
     */
    public function __construct($value, DependencyGraph $graph = null, $parents = [], $children = []) {
        $this->value = $value;

        foreach ($parents as $parent) {
            $this->parents[spl_object_hash($parent)] = $parent;
        }

        foreach ($children as $child) {
            $this->children[spl_object_hash($child)] = $child;
        }

        if (!$graph) {
            $graph = new DependencyGraph();
        }

        $this->graph = $graph;
    }

    /**
     * Get all of this nodes ancestors.
     * 
     * @return array
     */
    public function getAncestors() {
        $result = $this->getParents();

        foreach ($this->getParents() as $child) {
            $result = array_merge($result, $child->getAncestors());
        }

        return $result;
    }

    /**
     * Get the node's ancestors
     * 
     * @return array
     */
    public function getDescendents() {
        $result = $this->getChildren();

        foreach ($this->getChildren() as $child) {
            $result = array_merge($result, $child->getDescendents());
        }

        return $result;
    }

    /**
     * Returns true if $node is an ancestor of this node.
     * 
     * @param DependencyGraphNode $node
     * @return boolean
     */
    public function hasAncestor(DependencyGraphNode $node) {
        return array_key_exists(spl_object_hash($node), $this->getAncestors());
    }

    /**
     * Returns true if $node is a descendant of this node
     * 
     * @param DependencyGraphNode $node
     * @return boolean
     */
    public function hasDescendant(DependencyGraphNode $node) {
        return array_key_exists(spl_object_hash($node), $this->getDescendents());
    }

    /**
     * Add a child node to this node.
     * 
     * @param DependencyGraphNode $child
     * @return DependencyGraphNode
     * @throws CircularDependencyException If a circular dependency is detected
     */
    public function addChild(DependencyGraphNode $child) {
        $hash = spl_object_hash($child);

        if (isset($this->children[$hash])) {
            return $child;
        }

        /* test for circular dependency */
        if ($this->hasAncestor($child)) {
            throw new CircularDependencyException($this, $child, CircularDependencyException::OPPERATION_ADD_CHILD);
        }

        /* merge graphs at intersection */
        if ($child->getGraph() !== $this->getGraph()) {
            $this->getGraph()->merge($child->getGraph());
            $child->setGraph($this->getGraph());
        }

        $this->children[$hash] = $child;
        $child->addParent($this);


        return $child;
    }

    /**
     * Add a parent node to this node.
     *  
     * @param DependencyGraphNode $parent
     * @return DependencyGraphNode
     * @throws CircularDependencyException If a circular dependency is detected
     */
    public function addParent(DependencyGraphNode $parent) {
        $hash = spl_object_hash($parent);

        if (isset($this->parents[$hash])) {
            return $parent;
        }

        /* test for circular dependency */
        if ($this->hasDescendant($parent)) {
            throw new CircularDependencyException($this, $parent, CircularDependencyException::OPPERATION_ADD_PARENT);
        }

        /* if node is from another graph merge it into this graph */
        if ($parent->getGraph() !== $this->getGraph()) {
            $this->getGraph()->merge($parent->getGraph());
            $parent->setGraph($this->getGraph());
        }

        $this->parents[$hash] = $parent;
        $parent->addChild($this);

        return $parent;
    }

    public function addParents($parents) {
        array_map([$this, 'addParent'], $parents);
        return $this;
    }

    public function addChildren($children) {
        array_map([$this, 'addChild'], $children);
        return $this;
    }

    public function hasChild(DependencyGraphNode $node) {
        return array_key_exists(spl_object_hash($node), $this->children);
    }
    
    public function hasParent(DependencyGraphNode $node) {
        return array_key_exists(spl_object_hash($node), $this->parents);
    }
    
    public function getGraph() {
        return $this->graph;
    }

    public function setGraph(DependencyGraph $graph) {
        $this->graph = $graph;
    }

    /**
     * Is this node a root node. The node is a root node if it has no parents.
     * 
     * @return boolean
     */
    public function isRoot() {
        return empty($this->parents);
    }

    /**
     * Is this node a leaf node. The node is a leaf node if it has no children.
     * 
     * @return boolean
     */
    public function isLeaf() {
        return empty($this->children);
    }

    /**
     * Get the node's value
     * 
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Get an array of the node's parents.
     * 
     * @return array
     */
    public function getParents() {
        return $this->parents;
    }

    /**
     * Get an array of the node's children.
     * 
     * @return array
     */
    public function getChildren() {
        return $this->children;
    }

}
