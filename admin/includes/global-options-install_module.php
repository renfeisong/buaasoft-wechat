<?php
/**
 * This file is the install module page for the admin center.
 *
 * @author Bingchen Qin
 * @since 2.0.0
 */

?>

<style>

    #file-name {
        margin-bottom: 10px;
    }

    progress,
    progress::-webkit-progress-bar {
        display: block;
        background-color: #f3f3f3;
        border: 0;
        border-radius: 0;
        height: 20px;
        width: 250px;
        margin-bottom: 20px;
    }

    progress::-webkit-progress-value {
        display: block;
        background-color: #4b8df8;
        border: 0;
        border-radius: 0;
        height: 20px;
        width: 250px;
        margin-bottom: 10px;
    }

    progress::-moz-progress-bar {
        display: block;
        background-color: #4b8df8;
        border: 0;
        border-radius: 0;
        height: 20px;
        width: 250px;
        margin-bottom: 10px;
    }

</style>

<h2>安装模块</h2>

<h4 id="file-name"></h4>
<progress class="progress-bar hidden" value="10" max="100"></progress>

<button id="add-file" class="button blue-button button-with-icon"><i class="fa fa-plus fa-fw"></i> 选择文件...</button>
<button id="upload-file" class="button blue-button button-with-icon hidden"><i class="fa fa-upload fa-fw"></i> 开始上传</button>
<button id="uploading-file" class="button blue-button button-with-icon disabled-button hidden"><i class="fa fa-spinner fa-spin fa-fw"></i> 正在上传...</button>
<button id="upload-success" class="button green-button button-with-icon disabled-button hidden"><i class="fa fa-check fa-fw"></i> 安装成功</button>
<button id="upload-fail" class="button red-button button-with-icon disabled-button hidden"><i class="fa fa-close fa-fw"></i> 安装失败</button>

<form id="file-form" class="hidden" enctype="multipart/form-data">
    <input id="add-file-hidden" class="button blue-button" type="file" name="file" accept="application/zip"/>
</form>

<script>
    var error_message = {
        0: "模块安装成功",
        1: "上传过程中出现错误",
        2: "文件大小超过限制",
        3: "文件类型不是zip",
        4: "同名模块已存在",
        5: "文件解压时出现错误",
        6: "压缩包名、文件夹名和类名不相同",
        7: "未找到index.php文件",
        8: "模块类不是BaseClass的子类",
        100: "出现了未知错误"
    };

    $(document).ready(function() {

        $("#add-file").click(function () {
            $("#add-file-hidden").trigger('click');
        });

        $("#add-file-hidden").change(function() {
            $("#file-name").html(this.files[0].name).removeClass("hidden");
            $("#add-file").addClass("hidden");
            $("#upload-file").removeClass("hidden");
        });

        $("#upload-file").click(function() {
            $("#upload-file").addClass("hidden");
            $("#uploading-file").removeClass("hidden");
            $(".progress-bar").removeClass("hidden");
            var form_data = new FormData($("#file-form")[0]);
            $.ajax({
                url: "./includes/global-options-install_module-ajax.php",
                type: "POST",
                xhr: function() {
                    var file_xhr = $.ajaxSettings.xhr();
                    if (file_xhr.upload) { // Check if upload property exists
                        file_xhr.upload.addEventListener("progress", handle_progress, false);
                    }
                    return file_xhr;
                },
                data: form_data,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json"
            }).done(function(data) {
                if (data["code"] == 0) {
                    $("#uploading-file").addClass("hidden");
                    $(".progress-bar").addClass("hidden");
                    $("#upload-success").removeClass("hidden");
                } else {
                    toastr.error(error_message[data["code"]], "Error");
                    $("#uploading-file").addClass("hidden");
                    $(".progress-bar").addClass("hidden");
                    $("#upload-fail").removeClass("hidden");
                }
            });
        });

        function handle_progress(e) {
            if (e.lengthComputable) {
                $(".progress-bar").attr({
                    value:e.loaded,
                    max:e.total
                });
            }
        }


    });


</script>