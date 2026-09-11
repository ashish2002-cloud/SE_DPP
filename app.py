from flask import Flask, render_template, request, redirect, session, flash
import mysql.connector
from werkzeug.security import generate_password_hash, check_password_hash

app = Flask(__name__)

# Secret key for login sessions
app.secret_key = "dpp_secret_key_change_this_later"


# -----------------------------
# DATABASE CONNECTION
# -----------------------------

def get_db_connection():

    
    connection = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="dpp_platform"
    )

    return connection


# -----------------------------
# HOME PAGE
# -----------------------------

@app.route("/")
def home():

    if "user_id" in session:
        return redirect("/dashboard")

    return redirect("/login")


# -----------------------------
# REGISTRATION
# -----------------------------

@app.route("/register", methods=["GET", "POST"])
def register():

    if request.method == "POST":

        name = request.form["name"]
        email = request.form["email"]
        password = request.form["password"]
        role = request.form["role"]

        # Check empty fields
        if not name or not email or not password or not role:

            flash("Please fill all fields.")

            return redirect("/register")

        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)

        # Check if email already exists
        cursor.execute(
            "SELECT id FROM users WHERE email = %s",
            (email,)
        )

        existing_user = cursor.fetchone()

        if existing_user:

            cursor.close()
            connection.close()

            flash("Email already registered.")

            return redirect("/register")

        # Hash password
        password_hash = generate_password_hash(password)

        # Insert user
        cursor.execute(
            """
            INSERT INTO users
            (name, email, password_hash, role)
            VALUES (%s, %s, %s, %s)
            """,
            (name, email, password_hash, role)
        )

        connection.commit()

        cursor.close()
        connection.close()

        flash("Registration successful. Please login.")

        return redirect("/login")

    return render_template("register.html")


# -----------------------------
# LOGIN
# -----------------------------

@app.route("/login", methods=["GET", "POST"])
def login():

    if request.method == "POST":

        email = request.form["email"]
        password = request.form["password"]

        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)

        cursor.execute(
            "SELECT * FROM users WHERE email = %s",
            (email,)
        )

        user = cursor.fetchone()

        cursor.close()
        connection.close()

        # Check user and password
        if user and check_password_hash(
            user["password_hash"],
            password
        ):

            session["user_id"] = user["id"]
            session["name"] = user["name"]
            session["role"] = user["role"]

            return redirect("/dashboard")

        else:

            flash("Invalid email or password.")

            return redirect("/login")

    return render_template("login.html")


# -----------------------------
# DASHBOARD
# -----------------------------

@app.route("/dashboard")
def dashboard():

    if "user_id" not in session:

        return redirect("/login")

    return render_template(
        "dashboard.html",
        name=session["name"],
        role=session["role"]
    )


# -----------------------------
# LOGOUT
# -----------------------------

@app.route("/logout")
def logout():

    session.clear()

    return redirect("/login")


# -----------------------------
# RUN APPLICATION
# -----------------------------

if __name__ == "__main__":

    app.run(
        debug=True
    )