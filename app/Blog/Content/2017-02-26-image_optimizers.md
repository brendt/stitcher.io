---
type: news
title: 'Image optimizers'
---

I've been working lately on image optimisation in Stitcher. As a try-out, I've added [this library](https://github.com/psliwa/image-optimizer) to the responsive images module. 
 
Enabling the optimizer is done by updating Stitcher (1.0.0-alpha2), and adding the following parameter in `config.yml`.
 
```yaml
engines:
    optimizer: true
```
