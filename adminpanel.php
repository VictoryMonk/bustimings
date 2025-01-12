<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("Location: login.php"); // Redirect to login page if not logged in
  exit;
}

// Include the database connection file
include 'connection.php';

// Check if a delete request was made
if (isset($_GET['delete'])) {
  $stop_id = $_GET['stop_id'];
  $routes = $_GET['routes'];
  $arrival_time = $_GET['arrival_time'];

  $deleteQuery = "DELETE FROM stop_times WHERE stop_id = '$stop_id' AND routes = '$routes' AND arrival_time = '$arrival_time'";
  mysqli_query($conn, $deleteQuery);
  header("Location: adminpanel.php"); // Refresh the page after deletion
}

// Check if the add admin form was submitted
if (isset($_POST['add_admin'])) {
  $new_username = $_POST['new_username'];
  $new_password = $_POST['new_password'];

  // Hash the password for security
  $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

  // Insert the new admin into the database
  $addAdminQuery = "INSERT INTO users (username, password) VALUES ('$new_username', '$hashed_password')";

  if (mysqli_query($conn, $addAdminQuery)) {
    echo "<script>alert('New admin added successfully.');</script>";
  } else {
    echo "<script>alert('Error adding admin: " . mysqli_error($conn) . "');</script>";
  }
}

// Check if an edit request was made
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $routes = $_POST['routes'];
    $arrival_time = $_POST['arrival_time']; // Treat arrival_time as string

    // Use prepared statement to prevent SQL injection
    $updateQuery = "UPDATE stop_times SET routes = ?, arrival_time = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssi", $routes, $arrival_time, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: adminpanel.php"); // Refresh the page after editing
}

// Check if Admin Kill request was made
if (isset($_POST['admin_kill'])) {
    $admin_username = $_POST['admin_username'];

    // Only allow the admin named 'overlord' to perform this action
    if ($_SESSION['username'] === 'overl0rd') {
        $killAdminQuery = "DELETE FROM users WHERE username = ?";
        $stmt = $conn->prepare($killAdminQuery);
        $stmt->bind_param("s", $admin_username);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Admin $admin_username removed successfully.');</script>";
    } else {
        echo "<script>alert('You do not have permission to perform this action.');</script>";
    }
}


// Fetch all rows from the stop_times table
$query = "SELECT id, stop_id, routes, arrival_time FROM stop_times";
$result = mysqli_query($conn, $query);

// Fetch all admins for the Admin Kill functionality
$adminQuery = "SELECT username FROM users";
$adminResult = mysqli_query($conn, $adminQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - Stop Timings</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f0f0;
      margin: 0;
      padding: 0;
    }

    .container {
      width: 90%;
      margin: 50px auto;
      background-color: #fff;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      position: relative;
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    table,
    th,
    td {
      border: 1px solid #ddd;
    }

    th,
    td {
      padding: 12px;
      text-align: left;
    }

    th {
      background-color: #4CAF50;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f2f2f2;
    }

    tr:hover {
      background-color: #ddd;
    }

    .btn-container {
      display: flex;
      justify-content: flex-end;
    }

    .btn {
      background-color: #4CAF50;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-left: 10px;
      text-decoration: none;
    }

    .btn.edit {
      background-color: #007BFF;
    }

    .btn.delete {
      background-color: #FF4C4C;
    }

    form {
      display: inline;
    }

    /* Popup Styles */
    .popup {
      display: none;
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }

    .popup-content {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }

    .close-btn {
      background-color: #FF4C4C;
      color: white;
      border: none;
      padding: 10px 15px;
      cursor: pointer;
      border-radius: 4px;
    }

    /* Global Styles */

.container {
  width: 90%;
  margin: 50px auto;
  background-color: #fff;
  padding: 20px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  position: relative;
}

/* Desktop and Tablet Styles (default) */

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: left;
}

th {
  background-color: #4CAF50;
  color: white;
}

tr:nth-child(even) {
  background-color: #f2f2f2;
}

tr:hover {
  background-color: #ddd;
}

.btn-container {
  display: flex;
  justify-content: flex-end;
}

.btn {
  background-color: #4CAF50;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-left: 10px;
  text-decoration: none;
}

.edit-btn,
.save-btn,
.delete-btn {
  padding: 8px 15px;
}

/* Responsive Styles */

/* Tablet (768px - 991px) */
@media (max-width: 991px) {
  .container {
    margin: 30px auto;
  }
  table th,
  table td {
    padding: 8px;
  }
}

/* Mobile (480px - 767px) */
@media (max-width: 767px) {
  .container {
    margin: 20px auto;
    padding: 15px;
  }
  table {
    border: 0;
  }
  table caption {
    font-size: 1.3em;
  }
  table thead {
    border: none;
    clip: rect(0 0 0 0);
    height: 1px;
    margin: -1px;
    overflow: hidden;
    padding: 0;
    position: absolute;
    width: 1px;
  }
  table tr {
    border-bottom: 3px solid #ddd;
    display: block;
    margin-bottom: 0.625em;
  }
  table td {
    border-bottom: 1px solid #ddd;
    display: block;
    font-size: 0.8em;
    text-align: right;
  }
  table td:before {
    content: attr(data-label);
    float: left;
    font-weight: bold;
    text-transform: uppercase;
  }
  table td:last-child {
    border-bottom: 0;
  }
  .btn-container {
    flex-direction: column;
    align-items: flex-end;
  }
  .btn {
    margin-left: 0;
    margin-bottom: 10px;
    text-align: center;
  }
}

/* Mobile (max-width: 480px) */
@media (max-width: 480px) {
  .container {
    width: 95%;
    margin: 15px auto;
    padding: 10px;
  }
  table td {
    font-size: 0.7em;
  }
  .btn {
    padding: 6px 12px;
  }
}


      h1 {
        font-size: 24px;
      }
    
  </style>
  <script>
    function togglePopup() {
      const popup = document.getElementById("addAdminPopup");
      popup.style.display = (popup.style.display === "flex") ? "none" : "flex";
    }

    function toggleAdminKillPopup() {
      const popup = document.getElementById("adminKillPopup");
      popup.style.display = (popup.style.display === "flex") ? "none" : "flex";
    }
  </script>
  <link rel="stylesheet" href="adminpanel.css">
</head>

<body>

  <div class="container">
    <h1>Admin Panel - Stop Timings</h1>

    <button class="btn" onclick="togglePopup()">Admin+</button>
    <button class="btn" onclick="toggleAdminKillPopup()">Admin Kill</button>
    <a href="logout.php" class="btn" style="top: 20px; right: 20px;">Logout</a>

    <div class="popup" id="addAdminPopup">
      <div class="popup-content">
        <h2>Add New Admin</h2>
        <form method="POST" action="adminpanel.php">
          <input type="text" name="new_username" placeholder="Username" required>
          <input type="password" name="new_password" placeholder="Password" required>
          <button type="submit" name="add_admin">Add Admin</button>
          <button type="button" class="close-btn" onclick="togglePopup()">Close</button>
        </form>
      </div>
    </div>

    <div class="popup" id="adminKillPopup">
      <div class="popup-content">
        <h2>Admin Kill</h2>
        <form method="POST" action="adminpanel.php">
          <input type="text" name="admin_username" placeholder="Admin Username" required>
          <button type="submit" name="admin_kill">Remove Admin</button>
          <button type="button" class="close-btn" onclick="toggleAdminKillPopup()">Close</button>
        </form>
      </div>
    </div>


  <table id="stop-timings-table">
    <thead>
      <tr>
        <th>Id</th>
        <th>Stop ID</th>
        <th>Routes</th>
        <th>Arrival Time</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
  <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
      <td class="id"><?php echo $row['id']; ?></td> <!-- Use the 'id' column here -->
      <td class="stop-id"><?php echo $row['stop_id']; ?></td>
      <td class="routes" contenteditable="false"><?php echo $row['routes']; ?></td>
      <td class="arrival-time" contenteditable="false"><?php echo $row['arrival_time']; ?></td>
      <td>
        <button class="btn edit-btn" onclick="toggleEdit(this)">Edit</button>
        <button class="btn save-btn" style="display: none;" onclick="saveEdits(this)">Save</button>
        <form method="GET" action="adminpanel.php" style="display:inline;">
          <input type="hidden" name="id" value="<?php echo $row['id']; ?>"> <!-- Use 'id' for deletion -->
          <button type="submit" name="delete" class="btn delete-btn">Delete</button>
        </form>
      </td>
    </tr>
  <?php } ?>
</tbody>
  </table>

  <script>
    function toggleEdit(editBtn) {
      const row = editBtn.closest('tr');
      const routesCell = row.querySelector('.routes');
      const arrivalTimeCell = row.querySelector('.arrival-time');
      const saveBtn = row.querySelector('.save-btn');

      routesCell.setAttribute('contenteditable', 'true');
      arrivalTimeCell.setAttribute('contenteditable', 'true');
      editBtn.style.display = 'none';
      saveBtn.style.display = 'inline-block';
    }

    function saveEdits(saveBtn) {
  const row = saveBtn.closest('tr');
  const id = row.querySelector('.id').textContent; // Use 'id' column value
  const routesCell = row.querySelector('.routes');
  const arrivalTimeCell = row.querySelector('.arrival-time');
  const editBtn = row.querySelector('.edit-btn');

  const updatedRoutes = routesCell.textContent;
  const updatedArrivalTime = arrivalTimeCell.textContent;

  // Send AJAX request to update database using 'id' instead of 'stop_id'
  fetch(`update-stop-timing.php?id=${id}&routes=${updatedRoutes}&arrival_time=${updatedArrivalTime}`)
    .then(response => response.json())
    .then(data => {
      if (data.status === "success") {
        console.log(data.message); // Success message
      } else {
        console.error(data.message); // Error message
      }
    })
    .catch(error => console.error('Error:', error));

  routesCell.setAttribute('contenteditable', 'false');
  arrivalTimeCell.setAttribute('contenteditable', 'false');
  saveBtn.style.display = 'none';
  editBtn.style.display = 'inline-block';
}
  </script>

  </div>

</body>

</html>

<?php
// Close the database connection
mysqli_close($conn);
?>