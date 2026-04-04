# GitHub Flavored Markdown (GFM) Specification

## Introduction
GitHub Flavored Markdown (GFM) is a strict superset of CommonMark. It adds extensions that are commonly used on GitHub, such as tables, task lists, and strikethrough.

---

## 1. Basic Syntax

### Headings

# H1
## H2
### H3


### Paragraphs

Separate text with blank lines.

### Line Breaks

End a line with two spaces or use `<br>`.

---

## 2. Emphasis


*Italic* or _Italic_
**Bold** or __Bold__
***Bold Italic***
~~Strikethrough~~


---

## 3. Lists

### Unordered Lists


- Item
* Item
+ Item


### Ordered Lists


1. First
2. Second


### Nested Lists

Indent with 2–4 spaces.

---

## 4. Links


[Link text](https://example.com)


### Automatic Links


https://example.com


---

## 5. Images


![Alt text](image-url)


---

## 6. Code

### Inline Code


`code`


### Code Blocks

<pre>
language
code block

</pre>

---

## 7. Blockquotes


> This is a blockquote


---

## 8. Horizontal Rules


---
***
___


---

## 9. Tables (GFM Extension)


| Header 1 | Header 2 |
|----------|----------|
| Cell 1   | Cell 2   |


### Alignment


| Left | Center | Right |
|:-----|:------:|------:|


---

## 10. Task Lists (GFM Extension)


- [x] Completed
- [ ] Not completed


---

## 11. Strikethrough (GFM Extension)


~~text~~


---

## 12. Autolinks (GFM Extension)

GFM automatically converts URLs and email addresses into links.

---

## 13. Emoji (GitHub Feature)


:smile:
:rocket:


---


## 14. Mentions and References (GitHub Feature)


@username
#123 (issue reference)


---


## 15. Escaping Characters

Use backslash `\`:


\*not italic\*


---


## 16. HTML Support

Inline HTML is supported:

<div>HTML content</div>


---

## 17. Alert Extension

Support Github Flavored blockquote alerts:

> [!WARNING]
> You're doing something terribly dangerous!
> Please, stop right now!

It's available only if you install `pomodocs/commonmark-alert` extension.

---

## Notes

* GFM is based on CommonMark: [https://spec.commonmark.org/](https://spec.commonmark.org/)
* Additional GFM extensions are documented here:
  [https://github.github.com/gfm/](https://github.github.com/gfm/)


---


## License

This document is free to use and adapt.
