/* eslint-disable no-unused-vars */
import React from 'react';
import logo from '/src/assets/logo1.png';

import { NavLink } from 'react-router-dom';
import "./Navbar.css";
export const Navbar = () => {
  return (
      <nav>
        <div className='logo'>
        <img src={logo} alt="Logo" style={{ width: '150px', height: '100px' }} />
        </div>
          
  <ul>
      <li>
        <NavLink to="/" className="active">Accueil</NavLink>
        </li>
      <li>
        <NavLink to="/evenement">Évenement</NavLink>
        </li>
      <li>
        <NavLink to="/produit">Produit</NavLink>
        </li>
      <li>
        <NavLink to="/article">Articles</NavLink>
        </li>
      <li>
        <NavLink to="/forum">Forum</NavLink>
        </li>
  </ul>
  <div >
    
  <button className='button'>
        <NavLink to="/login" className='connexion'>Connexion</NavLink>
 </button>
       
  </div>
  
      </nav>
  );
}

export default Navbar
