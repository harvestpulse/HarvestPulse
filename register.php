<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HarvestPulse Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;

            background:
            linear-gradient(
                135deg,
                #052e16,
                #14532d,
                #166534,
                #22c55e
            );

            font-family:'DM Sans', sans-serif;
            overflow:hidden;
            padding:20px;
        }

        .login-card{
            width:420px;
            background:white;
            border-radius:28px;
            padding:45px;

            box-shadow:
            0 20px 60px rgba(0,0,0,0.25);

            position:relative;
        }

        .logo{
            display:flex;
            align-items:center;
            gap:10px;

            font-family:'Syne', sans-serif;
            font-size:28px;
            font-weight:800;

            color:#14532d;
            margin-bottom:15px;
        }

        .logo-dot{
            width:14px;
            height:14px;
            border-radius:50%;
            background:#22c55e;
            box-shadow:0 0 15px #22c55e;
        }

        .title{
            font-size:32px;
            font-weight:800;
            color:#111827;

            margin-bottom:10px;
        }

        .sub{
            color:#6b7280;
            margin-bottom:35px;
            line-height:1.5;
        }

        .input-group{
            margin-bottom:22px;
        }

        .input-group label{
            display:block;
            margin-bottom:8px;
            font-weight:600;
            color:#14532d;
        }

        .input-group input{
            width:100%;
            padding:16px;
            border-radius:14px;
            border:1px solid #d1d5db;

            font-size:15px;
            outline:none;
            transition:0.2s;
        }

        .input-group input:focus{
            border-color:#22c55e;
            box-shadow:0 0 0 4px rgba(34,197,94,0.15);
        }

        .login-btn{
            width:100%;
            padding:16px;
            border:none;
            border-radius:16px;

            background:#22c55e;
            color:white;

            font-size:16px;
            font-weight:700;
            cursor:pointer;

            transition:0.2s;
        }

        .login-btn:hover{
            background:#16a34a;
            transform:translateY(-2px);
        }

        .bottom-text{
            margin-top:22px;
            text-align:center;
            color:#6b7280;
            font-size:14px;
        }

        .create-account{
            margin-top:20px;
            text-align:center;
            font-size:14px;
            color:#6b7280;
        }

        .create-account a{
            color:#16a34a;
            font-weight:700;
            text-decoration:none;
        }

        .create-account a:hover{
            text-decoration:underline;
        }

        .leaf{
            position:absolute;
            font-size:120px;
            opacity:0.06;
            right:-10px;
            top:-20px;
            transform:rotate(-15deg);
        }

    </style>
</head>

<body>

    <div class="login-card">

        <div class="leaf">🌿</div>

        <div class="logo">
            <span class="logo-dot"></span>
            HarvestPulse
        </div>

        <div class="title">
            Welcome Back
        </div>

        <div class="sub">
            Login with your South African ID number to continue managing your harvest listings.
        </div>

        <form action="login_process.php" method="POST">

            <div class="input-group">
                <label>ID Number</label>

                <input 
                    type="text" 
                    name="id_number" 
                    maxlength="13"
                    placeholder="Enter your 13-digit ID number"
                    required
                >
            </div>

            <button type="submit" class="login-btn">
                Login
            </button>

        </form>

        <div class="create-account">
            Don’t have an account?
            <a href="register.php">Create Account</a>
        </div>

        <div class="bottom-text">
            Fresh produce. Smart auctions. Better farming.
        </div>

    </div>

</body>
</html>