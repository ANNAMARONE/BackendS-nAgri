/* eslint-disable no-unused-vars */
import './App.css'
import Navbar from './components/Navbar/Navbar'
import { BrowserRouter, Route, Routes} from 'react-router-dom'

import Evenement from './components/pages/event/Evenement'
import Produit from './components/pages/e-commerce/Produit'
import Article from './components/pages/article/Article'
import Forum from './components/pages/forum/Forum'
import Login from './components/pages/Auth/Login'
import Register from './components/pages/Auth/Register'
import Accueil from './components/pages/Home/Accueil'
function App() {

  return (
    <div className='App'>
      
     <Navbar/>
     <Routes>
     <Route path='/' element={<Accueil/>}/>
     <Route path='/evenement' element={<Evenement/>}/>
     <Route path='/produit' element={<Produit/>}/>
     <Route path='/article' element={<Article/>}/>
     <Route path='/forum' element={<Forum/>}/>
     <Route path='/login' element={<Login/>}/>
     <Route path='/register' element={<Register/>}/>
     </Routes>
    </div>
  )

}

export default App
