# Portfolio

A portfolio management plugin for WordPress.

I wrote this fairly complex plugin in 2010 but — although it has been in production-use since then — I never managed to finish it and sort out the remaining issues.

It was written for WordPress 3.0 and may or may not work with later releases.

## Features

* Tight integration into WordPress UI
* Add/remove projects to categories via drag and drop
* Complete design freedom to customise frontend
* Media manager, all uploaded assets are separate from the WordPress media manager

## Installation

1. Copy the files into a directory inside wp-content/plugins 
2. Activate plugin

## How to use

Use the following public API functions (defined in [portfolio.php](https://github.com/matthiassiegel/portfolio/tree/master/portfolio.php)) in your WordPress templates:

* $portfolio->getProject()
* $portfolio->getProjects()
* $portfolio->getCategory()
* $portfolio->getCategories()
* $portfolio->getMedia()

## Screenshots

<div>
	<h3>Project list</h3>
	<img src="https://github.com/matthiassiegel/portfolio/raw/master/screenshot-1.png" alt="Project list">
</div>

<div>
	<h3>Edit project</h3>
	<img src="https://github.com/matthiassiegel/portfolio/raw/master/screenshot-2.png" alt="Edit project">
</div>

<div>
	<h3>Category management</h3>
	<img src="https://github.com/matthiassiegel/portfolio/raw/master/screenshot-3.png" alt="Category management">
</div>

<div>
	<h3>Media manager</h3>
	<img src="https://github.com/matthiassiegel/portfolio/raw/master/screenshot-4.png" alt="Media manager">
</div>

## To do

* Test with newer WordPress releases
* Fix media manager screens
* Add 'About' page

## Copyright
Copyright (c) 2010-2012 Matthias Siegel.
See [LICENSE](https://github.com/matthiassiegel/portfolio/tree/master/LICENSE.md) for details.