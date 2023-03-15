import React from "react";
import { TextField, Button, Typography } from "@mui/material";

function RegisterForm() {
  const [formData, setFormData] = React.useState({
    email: "",
    password: "",
  });

  const handleChange = (event) => {
    const { name, value } = event.target;
    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }));
  };

  const handleSubmit = async (event) => {
    event.preventDefault();

    const response = await fetch("/api/auth/register.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(formData),
    });

    const data = await response.json();

    if (response.ok) {
      localStorage.setItem("accessToken", data.access_token);
      window.location.reload();
    } else {
      alert(data.message);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <Typography variant="h5" gutterBottom>
        Registration
      </Typography>
      <TextField
        type="email"
        name="email"
        label="Email"
        variant="outlined"
        margin="normal"
        fullWidth
        required
        value={formData.email}
        onChange={handleChange}
      />
      <TextField
        type="password"
        name="password"
        label="Пароль"
        variant="outlined"
        margin="normal"
        fullWidth
        required
        value={formData.password}
        onChange={handleChange}
      />
      <Button type="submit" variant="contained" color="primary">
        Register
      </Button>
    </form>
  );
}

export default RegisterForm;