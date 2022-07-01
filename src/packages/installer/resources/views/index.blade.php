<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>WIMS Installer</title>
    <link rel="icon"
        href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAADzElEQVR4Ae3BwYucZx0A4Gfenc335hC6PQjiYf0DFg/iEvAgiHhQCgoeegl4sHhVwVP6H3gx4M14EISmIkIKFkKgXWjOimgOJR4EawgmikHJzs47k+zPhSls8833Ju7KO5LJPo8zZ8681Eaebwev4YKFsBCOhWPhaWEhHAsLYSEQFgJhIRAWAmEhHAvHwrHAAT7AHRUjz/YGfooNL7Zf4bv4t54NdZ/BTZzz4tvB5/GWnqTudWTr42v4ip6kbtf6eU1PUvdp62dbT1K3af0c6knqxtbPRE9St2n9HOhJ6sbWz1RPUpesnwM9Sd3Y+pnqGavbdEI7OzsuX76spVu3brl69apTmugZqxs7oe3tbZcuXdJSRLh27ZpHjx45hamepG7DCeWctfb48WOj0cgpTfQkdWMn1HWd1ubzuYhwSlM9Sd2mE8o5a20+n/sfHOgZqxs7ofv377t586bxeKzmwoULLl68aMjt27c9ePDAs9y5c0dEOKUDPWN1G07oxo0b9vb2nDt3Ts3u7q69vT1Drly54vr16548eeJZZrOZU5rqGavbcAqlFKUUNYeHh2pKKfb3983nc41M9CR1mxrouk5NKUVEaGiqJxn2fXQayDmrKaWICA1N9CTLXsePNZJzVlNKEREaKnqSp30Zv0DSSNd1amazmYjQ0FRPcuxzuI5OQzlnNaUUEaGhN/UkC6/gXWxpLOesppSisR/gmz4hWfg2tq1A13VqptOpxkb4OT7rY8nCxIrknNVMp1Mr8CreRnIkWfglPrICXdcZEhFKKVbki/i6I8nCPn5oBXLOhsznc4eHh1boC44kx36N9zSWczaklCIirNBtR5KnfQ9zDXVdZ0gpRURYkbfxjiPJ0z7ETzSUczaklGJFfos3EI4ky36EmUZyzobMZjMRobF/4Fs48LFk2d/xoUa6rjOklCIiNPYd/NUnJMNGGsk5G1JKEREaegu/0ZMMyxrJORtSShERGnmCNw1IhnUa6brOkFKKiNDIBB8ZkAw7r5GcsyGz2cz/QzKs00jO2ZBSiojQSKhIhp3XSNd1hpRSRIRVS5YlbGok52xIKUVEaCRUjC3LGGnk7t27JpOJvnv37okIqzay7FX8UyOj0cjW1pYhBwcHptOpBv6FLQPGlp3XUER4+PChFQsVybLOSyRZdt5LJFnWWT+hIlmWvUSSZdn6CRXJsmz9TFQkyzatnz+pSJb9xfp5V0Wy7I/4vfXxZ/xMxYZh7+Or+JQX2x/wDfxNxUjdBr6EXbyC8HzhvxdOJzzfPn6HD3DozJkzZyr+A9o7Qqy6izg2AAAAAElFTkSuQmCC" />
    <meta name="viewport" content="width=device-width" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script type="module" crossorigin src="{{ asset('vendor/installer/index.9c793cbf.js') }}"></script>
    <link rel="modulepreload" href="{{ asset('vendor/installer/vendor.968a0644.js') }}">
    <link rel="stylesheet" href="{{ asset('vendor/installer/vendor.7628f02f.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/installer/index.8626dda2.css') }}">
    <style>
        body,
        .noscript {
            display: flex;
            min-width: 1000px;
            min-height: 650px;
            align-items: center;
            justify-content: center;
            background: hsl(217, 71%, 53%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        .noscript h1 {
            color: #FFF;
            line-height: 2;
            font-weight: 100;
            text-align: center;
        }

    </style>
</head>

<body>
    <noscript>
        <div class="noscript">
            <h1 class="msg">
                We're sorry!<br>
                <small>but installer doesn't work without JavaScript.</small>
                <br>Please enable JavaScript to continue!
            </h1>
        </div>
    </noscript>
    <div id="app"></div>
</body>

</html>
