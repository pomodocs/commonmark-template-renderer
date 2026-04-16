<?php

declare(strict_types=1);

/*
 * This file is part of the pomodocs/commonmark-template-renderer package.
 * MIT License. For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

dataset('description_list', [<<<MARKDOWN
Apple
:   Pomaceous fruit of plants of the genus Malus in
    the family Rosaceae.
:   An American computer company.

Orange
:   The fruit of an evergreen tree of the genus Citrus.
:   A color between red and yellow in the spectrum.
:   A programming language created by Apple.

Blue
:   The color of the clear sky and the deep sea.
:   An old Italian phone company.
MARKDOWN
]);

dataset('footnotes', [
<<<MARKDOWN
This is a footnote reference,[^1] and another.[^longnote]

[^1]: Here is the footnote.
[^longnote]: Here's one with multiple paragraphs and code.

    Indented paragraphs are also part of the footnote.

        ```python
        def hello_world():
            print("Hello, world!")
        ```

MARKDOWN
]);

dataset('front_matter', [<<<MARKDOWN
---
title: "My Document"
author: "Cristiano Cinotti"
date: "2026-04-08"
---

# Hello World

This is a sample document with front matter.
MARKDOWN
]);

dataset('toc', [<<<MARKDOWN
# Chapter 1

This is the first chapter.

## Chapter 2

This is the second chapter.

### Chapter 3

This is the third chapter.
MARKDOWN
]);

dataset('tables', [<<<MARKDOWN
| header 1 | header 2 | header 2 |
| :------- | :------: | -------: |
| cell 1.1 | cell 1.2 | cell 1.3 |
| cell 2.1 | cell 2.2 | cell 2.3 |
MARKDOWN
]);

dataset('tasks', [<<<MARKDOWN
- [ ] Task 1
- [x] Task 2
- [ ] Task 3
MARKDOWN
]);
