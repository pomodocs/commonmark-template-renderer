# CommonMark Specification

## Introduction

CommonMark is a strongly defined, unambiguous specification of the Markdown language. It precisely defines how Markdown text should be parsed and converted into HTML.


## 1. Document Structure

A document consists of:
- block elements
- inline elements


## 2. Blocks

### 2.1 Paragraphs

A paragraph is one or more consecutive lines of text separated by one or more blank lines.


This is a paragraph.

This is another one.

---

### 2.2 Headings

#### ATX (using `#`)



# H1

## H2

### H3



- 1 to 6 `#` characters
- A space after `#` is required

#### Setext


# Heading H1

## Heading H2


---

### 2.3 Block Quotes


> This is a block quote
> spanning multiple lines


- Can be nested
- Can contain other block elements

---

### 2.4 Lists

#### Unordered Lists


* item

- item

* item


#### Ordered Lists


1. first
2. second



- The starting number does not affect rendering

#### Nested Lists

Indentation of at least 2–4 spaces

---

### 2.5 Code Blocks

#### Indented Code Block

Indentation of 4 spaces:




code


``

#### Fenced Code Block

```markdown
```python
print("hello")
`````

```

- Uses backticks (```) or tildes (`~~~`)
- Optional language hint

---

### 2.6 Horizontal Rules


---
***
___


- At least 3 characters

---

### 2.7 Tables (common extension, not core)


| Col1 | Col2 |
|------|------|
| A    | B    |


---

### 2.8 Inline HTML and HTML Blocks

Raw HTML is allowed:


<div>
Raw HTML
</div>


---

## 3. Inline Elements

### 3.1 Emphasis


*italic* or _italic_
**bold** or __bold__


---

### 3.2 Inline Code


`code`


---

### 3.3 Links


[link](https://example.com)


#### With title


[link](https://example.com "title")


#### Reference-style


[link][id]

[id]: https://example.com


---

### 3.4 Images


![alt text](url)


---

### 3.5 Escaping


\*not italic\*


Escapable characters include:


\ ` * _ { } [ ] ( ) # + - . ! |


---

### 3.6 Hard Line Break

Two trailing spaces:


line 1␠␠
line 2


---

### 3.7 Soft Break

A single newline becomes a space

---

## 4. Parsing and Rules

### 4.1 Precedence

- Blocks are parsed before inline elements
- Some constructs take precedence (e.g., code blocks vs lists)

---

### 4.2 Ambiguity Resolution

CommonMark defines:
- how `_` and `*` behave
- how spacing and indentation are interpreted
- precise rules for lists and nesting

---

### 4.3 Whitespace

- Leading spaces can affect meaning
- A tab equals 4 spaces

---

## 5. HTML Entities


&amp;
&lt;
&gt;


Supported and preserved

---

## 6. Autolinks


<https://example.com>
<user@example.com>


---

## 7. Security

CommonMark:
- does not sanitize HTML
- leaves sanitization to the renderer

---

## 8. Differences from Original Markdown

CommonMark:
- removes ambiguities
- defines strict parsing rules
- improves interoperability

---

## 9. Non-standard Extensions (not part of core)

- Tables
- Strikethrough (`~~text~~`)
- Task lists (`- [ ]`)
- Footnotes

---

## 10. References

Official spec:
https://spec.commonmark.org/

Interactive tool:
https://spec.commonmark.org/dingus/
