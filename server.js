const express = require('express');
const cors = require('cors');
const dotenv = require('dotenv');
dotenv.config();

const app = express();
const port = process.env.PORT || 3000;

app.use(cors());
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Rotas
const sociosRoutes = require('./routes/sociosRoutes');
const diretoriaRoutes = require('./routes/diretoriaRoutes');
const noticiasRoutes = require('./routes/noticiasRoutes');
const historiaRoutes = require('./routes/historiaRoutes');
const esportivaRoutes = require('./routes/esportivaRoutes');
const esportivasRoutes = require('./routes/esportivasRoutes');
const eventosRoutes = require('./routes/eventosRoutes');
const estatutoRoutes = require('./routes/estatutoRoutes');
const sliderRoutes = require('./routes/sliderRoutes');
const configRoutes = require('./routes/configRoutes');
const indexRoutes = require('./routes/indexRoutes');

app.use('/api/socios', sociosRoutes);
app.use('/api/diretoria', diretoriaRoutes);
app.use('/api/noticias', noticiasRoutes);
app.use('/api/historia', historiaRoutes);
app.use('/api/esportiva', esportivaRoutes);
app.use('/api/esportivas', esportivasRoutes);
app.use('/api/eventos', eventosRoutes);
app.use('/api/estatuto', estatutoRoutes);
app.use('/api/slider', sliderRoutes);
app.use('/api/config', configRoutes);
app.use('/api/index', indexRoutes);

app.get('/', (req, res) => {
    res.json({ message: 'API da ASSGA funcionando!' });
});

app.listen(port, () => {
    console.log(`🚀 Servidor rodando na porta ${port}`);
});
