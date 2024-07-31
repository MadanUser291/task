<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'task') }}</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                Create User
            </div>
            <div class="card-body">
                <div id="successMessage" class="alert alert-success" style="display: none;"></div>
                <form id="userForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="Enter name">
                        <span class="text-danger" id="nameError"></span>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" name="email" class="form-control" id="email" placeholder="Enter email">
                        <span class="text-danger" id="emailError"></span>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" name="phone" class="form-control" id="phone" placeholder="Enter phone number" max="10">
                        <span class="text-danger" id="phoneError"></span>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control" id="description" rows="3" placeholder="Enter description"></textarea>
                        <span class="text-danger" id="descriptionError"></span>
                    </div>
                    <div class="form-group">
                        <label for="role_id">Role</label>
                        <select name="role_id" class="form-control" id="role_id">
                            <option value="">Select Role</option>
                            <option value="1">User</option>
                        </select>
                        <span class="text-danger" id="roleError"></span>
                    </div>
                    <div class="form-group">
                        <label for="profile_image">Profile Image</label>
                        <input type="file" name="profile_image" class="form-control-file" id="profile_image">
                        <span class="text-danger" id="profileImageError"></span>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                Users List
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="usersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Description</th>
                            <th>Role</th>
                            <th>Profile Image</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            function loadUsers() {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('users.index') }}",
                    success: function(users) {
                    var rows = '';

                    if (users.length === 0) {
                        rows = '<tr><td colspan="6" class="text-center"> No Record Found!.</td></tr>';
                    } else {
                        users.forEach(user => {
                            rows += '<tr>' +
                                '<td>' + user.name + '</td>' +
                                '<td>' + user.email + '</td>' +
                                '<td>' + user.phone + '</td>' +
                                '<td>' + user.description + '</td>' +
                                '<td>' + (user.role ? user.role.name : '') + '</td>' +
                                '<td>' + (user.profile_image ? '<img src="/storage/' + user.profile_image + '" width="50">' : '') + '</td>' +
                            '</tr>';
                        });
                    }

                    $('#usersTable tbody').html(rows);
                }
                    });
                }

            loadUsers();

            $('#userForm').on('submit', function(e) {
                e.preventDefault();

                $('.text-danger').text('');

                var isValid = true;
                var name = $('#name').val();
                var email = $('#email').val();
                var phone = $('#phone').val();
                var description = $('#description').val();
                var role_id = $('#role_id').val();
                var profile_image = $('#profile_image').val();

                if (name == '') {
                    $('#nameError').text('Name is required.');
                    isValid = false;
                }

                if (email == '') {
                    $('#emailError').text('Email is required.');
                    isValid = false;
                } else if (!validateEmail(email)) {
                    $('#emailError').text('Invalid email format.');
                    isValid = false;
                }

                if (phone == '') {
                    $('#phoneError').text('Phone number is required.');
                    isValid = false;
                } else if (!/^[6-9]\d{9}$/.test(phone)) {
                    $('#phoneError').text('Invalid Indian phone number.');
                    isValid = false;
                }

                if (role_id == '') {
                    $('#roleError').text('Role ID is required.');
                    isValid = false;
                } else if (isNaN(role_id)) {
                    $('#roleError').text('Role ID must be numeric.');
                    isValid = false;
                }

                if (profile_image != '') {
                    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
                    if (!allowedExtensions.exec(profile_image)) {
                        $('#profileImageError').text('Invalid file type. Allowed types: jpg, jpeg, png, gif.');
                        isValid = false;
                    }
                }

                if (isValid) {
                    var formData = new FormData(this);
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('users.store') }}",
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            $('#successMessage').text('User created successfully').show();
                            $('#userForm')[0].reset();
                            rows = '';
                            $('#usersTable tbody').html(rows);
                            appendUser(response.user);
                        },
                        error: function(response) {
                            if (response.responseJSON.errors) {
                                $('#nameError').text(response.responseJSON.errors.name);
                                $('#emailError').text(response.responseJSON.errors.email);
                                $('#phoneError').text(response.responseJSON.errors.phone);
                                $('#descriptionError').text(response.responseJSON.errors.description);
                                $('#roleError').text(response.responseJSON.errors.role_id);
                                $('#profileImageError').text(response.responseJSON.errors.profile_image);
                            }
                        }
                    });
                }
            });

            function validateEmail(email) {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(String(email).toLowerCase());
            }
        });

        function appendUser(user) {
        var row = '<tr>' +
                    '<td>' + user.name + '</td>' +
                    '<td>' + user.email + '</td>' +
                    '<td>' + user.phone + '</td>' +
                    '<td>' + user.description + '</td>' +
                    '<td>' + (user.role ? user.role.name : '') + '</td>' +
                    '<td>' + (user.profile_image ? '<img src="/storage/' + user.profile_image + '" width="50">' : '') + '</td>' +
                '</tr>';
        $('#usersTable tbody').append(row);
    }
        </script>
</body>
</html>
