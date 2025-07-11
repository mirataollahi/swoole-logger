# ⚡ Swoole Logger

A **high-performance, non-blocking logger** designed for [Swoole](https://www.swoole.co.uk/) applications in PHP. Built for speed and designed to handle logs asynchronously with minimal overhead.

> 🧠 Ideal for real-time applications, microservices, game servers, and long-running daemons built with PHP + Swoole.

---

## 🚀 Features

- Asynchronous logging with optional buffer
- Zero I/O blocking during log write
- Structured log data (level, tags, timestamp, service name)
- Supports multiple output printers (default: Console)
- Color-coded logs by level
- Easily extensible for custom outputs (e.g., File, Elasticsearch)
- Lightweight and production-ready

---

## 📦 Installation

```bash
composer require craftix/logger
