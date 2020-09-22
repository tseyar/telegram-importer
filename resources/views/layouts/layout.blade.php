<style>
    html, body {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        direction: rtl;
        text-align: right;
    }
    .container {
        height: auto;
        width: 100%;
        color: #fff;
    }
    .toolbar {
        background: #00b8d4;
        position: absolute;
        top: 0;
        bottom: 30px;
        width: 100%;
    }
    .sidebar {
        background: #555;
        position: absolute;
        top: 31px;
        bottom: 0;
        width: 20%;
    }
    .content {
        background: #ccc;
        position: absolute;
        top: 31px;
        right: 20%;
        bottom: 0;
        width: 80%;
    }

</style>

<div class="container">
    <div class="toolbar">Toolbar</div>
    <div class="sidebar">Sidebar</div>
    <div class="content">Content</div>
</div>
