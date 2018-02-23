Integrating the [Responsive Images spec](*https://responsiveimages.org/) together with CSS backgrounds, allowing for more flexibility for eg. hero images because you can use `background-size: cover;` etc., and still have the full benefits of responsive image loading.

```html
<html>
<head>
    <style>
        img {
            width:100%;
        }
        img.loaded {
            display: none;
        }
        .responsive-image {
            width:100%;
            height:500px;
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body>
    <div class="responsive-image">
        <img src="./small.jpg" srcset="./large.png 3000w, ./medium.jpg 1920w, ./small.jpg 425w" >
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.responsive-image');
            
            [].forEach.call(images, function (imageContainer) {
                const image = imageContainer.querySelector('img');
                
                image.addEventListener('load', function () {
                    if (!image.currentSrc) {
                        return;
                    }
                    
                    imageContainer.style['background-image'] = "url('" + image.currentSrc + "')";
                    image.classList.add('loaded');
                });
            })
        });
    </script>
</body>
</html>
```

