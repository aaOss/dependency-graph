# Dependency Graph

A dependency graph data structure.

Usage
---
Basic Usage
~~~php
use AlmostAnything\DependencyGraph\DependencyGraphNode as Node;

$jq = new Node('jquery.js');
$bs = new Node('bootstrap.js');
$dp = new Node('datepicker.js');

$js->addChild($bs);
$dp->addParents($bs, $jq);

$graph = $jq->getGraph();             // returns an instance of DependencyGraph
var_dump($graph->topologicalSort());

~~~

Dependency Graphs can be disconnected so you may want to provide a DependencyGraph instance.

~~~php
use AlmostAnything\DependencyGraph\DependencyGraphNode as Node;
use AlmostAnything\DependencyGraph\DependencyGraphGraph as Graph;

$graph = new Graph();

$jq = new Node('jquery.js', $graph);
$pt = new Node('prototype.js', $graph);

$jq->addChild(new Node('bootstrap.js'));      // $graph will be automatically set on child
$pt->addChild(new Node('pt-typeahead.js');

~~~

