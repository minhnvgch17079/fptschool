<h1>Upload file</h1>
<form action="upload" METHOD="POST", enctype="multipart/form-data">
    @csrf
    <input type="file" name ="file" multiple=""> <br> <br>
    <button type="submit"> Upload File</button>
</form>
