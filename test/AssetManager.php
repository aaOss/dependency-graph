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

/**
 * Test class for dependency graph
 */
class AssetManager {

    public $scripts = [];

    /**
     * Register a scrpt w/ the asset manager
     */
    public function addScript($id, $src, $deps = []) {
        if ($deps && !is_array($deps)) {
            $deps =  [$deps];
        }

        $this->scripts[$id] = [
            'src' => $src, 'deps' => $deps, 'use' => false
        ];
        
        return $this;
    }

    /**
     * Mark a script to be outputted this request.
     */
    public function useScript($id) {
        if (isset($this->scripts[$id])) {
            $this->scripts[$id]['use'] = true;
        }
    }

    /**
     * Return a top-down ordered array of scripts to use
     */
    public function scripts() {
        $graph = $this->buildGraph();

        if (!$graph) {
            return [];
        }

        /*
         * Flatten the graph into an array of scripts to output.
         * 
         * A topological sort will yield the correct order to output the scripts 
         */

        $result = [];

        foreach ($graph->topologicalSort() as $node) {
            if ($node->use) {
                $result [] = $node->getValue();
            }
        }

        return $result;
    }

    public function buildGraph() {
        /* map of script nodes */
        $graph = [];

        /* create nodess from config */
        foreach ($this->scripts as $id => $script) {
            $graph[$id] = $node = new Node((object) $script);
        }

        /* link scripts to their dependencies */
        foreach ($graph as $id => $node) {
            foreach ($node->getValue()->deps as $dep) {
                if (isset($graph[$dep])) {
                    $node->addParent($graph[$dep]);
                }
            }
        }

        /* set the use property on ancestors of used scripts */
        foreach ($graph as $node) {
            if ($node->getValue()->use) {
                foreach ($node->getAncestors() as $anc) {
                    $anc->getValue()->use = true;
                }
            }
        }

        /* return a reference to the graph object */
        
        if (!$graph) {
            return null;
        }
        
        $node = array_shift($graph);

        return $node->getGraph();
    }

}