@if (Session::has('success'))
    <script>
        $(document).ready(() => {
            $.notify({
                // options
                message: "{{ Session::get('success') }}",
            },{
                // settings
                showProgressbar: true,
                placement: {
                    from: 'top',
                    align: 'left',
                },
                mouse_over: 'pause',
                type: 'success',
                template: `
                <div data-notify="container" class="col-11 col-sm-3 alert alert-primary animated fadeInDown" role="alert"
                data-notify-position="top-left"
                style="display: inline-block; margin: 0px auto; position: fixed; transition: all 0.5s ease-in-out 0s; z-index: 1031; top: 20px; left: 20px; animation-iteration-count: 1;">
                <button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button><span
                data-notify="icon"></span>  <span
                data-notify="message">{2}!</span><a href="#" target="_blank" data-notify="url"></a>
                </div>`
            });
        })
    </script>
@elseif (Session::has('danger'))
    <script>
        $(document).ready(() => {
            $.notify({
                // options
                message: "{{ Session::get('danger') }}",
            },{
                // settings
                showProgressbar: true,
                placement: {
                    from: 'top',
                    align: 'left',
                },
                mouse_over: 'pause',
                type: 'danger',
                template: `
                <div data-notify="container" class="col-11 col-sm-3 alert alert-{0} animated fadeInDown" role="alert"
                data-notify-position="top-left"
                style="display: inline-block; margin: 0px auto; position: fixed; transition: all 0.5s ease-in-out 0s; z-index: 1031; top: 20px; left: 20px; animation-iteration-count: 1;">
                <button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button><span
                data-notify="icon"></span>  <span
                data-notify="message">{2}!</span><a href="#" target="_blank" data-notify="url"></a>
                </div>`
            });
        })
    </script>
@elseif (Session::has('warning'))
    <script>
        $(document).ready(() => {
            $.notify({
                // options
                message: "{{ Session::get('warning') }}",
            },{
                // settings
                showProgressbar: true,
                placement: {
                    from: 'top',
                    align: 'left',
                },
                mouse_over: 'pause',
                type: 'warning',
                template: `
                <div data-notify="container" class="col-11 col-sm-3 alert alert-{0} animated fadeInDown" role="alert"
                data-notify-position="top-left"
                style="display: inline-block; margin: 0px auto; position: fixed; transition: all 0.5s ease-in-out 0s; z-index: 1031; top: 20px; left: 20px; animation-iteration-count: 1;">
                <button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button><span
                data-notify="icon"></span>  <span
                data-notify="message">{2}!</span><a href="#" target="_blank" data-notify="url"></a>
                </div>`
            });
        })
    </script>
@elseif (Session::has('info'))
    <script>
        $(document).ready(() => {
            $.notify({
                // options
                message: "{{ Session::get('info') }}",
            },{
                // settings
                showProgressbar: true,
                placement: {
                    from: 'top',
                    align: 'left',
                },
                mouse_over: 'pause',
                type: 'info',
                template: `
                <div data-notify="container" class="col-11 col-sm-3 alert alert-{0} animated fadeInDown" role="alert"
                data-notify-position="top-left"
                style="display: inline-block; margin: 0px auto; position: fixed; transition: all 0.5s ease-in-out 0s; z-index: 1031; top: 20px; left: 20px; animation-iteration-count: 1;">
                <button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button><span
                data-notify="icon"></span>  <span
                data-notify="message">{2}!</span><a href="#" target="_blank" data-notify="url"></a>
                </div>`
            });
        })
    </script>
@endif
