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
 *
 * @author Rhys
 */
class CircularDependencyException extends \Exception {
    const OPPERATION_ADD_PARENT = 1;
    const OPPERATION_ADD_CHILD = 2;
    
    public $subject;
    public $added;
    public $type;
    
    public function __construct(DependencyGraphNode $subject, DependencyGraphNode $node, $type) {
        $this->subject = $subject;
        $this->added = $node;
        $this->type = $type;
        
        switch ($type) {
            case self::OPPERATION_ADD_CHILD:
                $msg = 'A circular dependency has been detected while adding a child node';
                break;
            case self::OPPERATION_ADD_PARENT:
                $msg = 'A circular dependency has been detected while adding a parent node';
                break;
        }
        
        parent::__construct($msg, null, null);
    }

}
