import { Route, Routes } from 'react-router-dom'
import Header from './components/Header'
import Nav from './components/Nav'
import Home from './components/Home'
import Footer from './components/Footer'

function App() {
  return (
    <div className="App">
      <header>
        <Header />
      </header>
      
      <section>
        <Nav />
      </section>
      
      <section>
        <Routes>
          <Route exact path="/" element={<Home />}></Route>
        </Routes>
      </section>
      
      <footer>
        <Footer />
      </footer>
      
    </div>
  );
}

export default App;