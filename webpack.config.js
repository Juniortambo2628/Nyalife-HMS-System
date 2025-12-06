const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');
const CompressionPlugin = require('compression-webpack-plugin');
const { PurgeCSSPlugin } = require('purgecss-webpack-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const glob = require('glob');

const webpack = require('webpack');

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';
  const shouldAnalyze = process.env.ANALYZE === 'true';
  
  // Get APP_PATH from environment variable, default to empty string for root deployment
  const appPath = process.env.APP_PATH || '';
  // Ensure publicPath starts and ends with /, and includes APP_PATH if set
  const publicPath = appPath 
    ? (appPath.endsWith('/') ? appPath : appPath + '/') + 'assets/dist/'
    : '/assets/dist/';
  
  return {
    entry: {
      // Core modules
      'app': './assets/js/app.js',
      'core': './assets/js/core/index.js',
      
      // Dashboards
      'dashboard-admin': './assets/js/dashboards/admin-dashboard.js',
      'dashboard-doctor': './assets/js/dashboards/doctor-dashboard.js',
      'dashboard-nurse': './assets/js/dashboards/nurse-dashboard.js',
      'dashboard-lab': './assets/js/dashboards/lab-technician-dashboard.js',
      'dashboard-pharmacist': './assets/js/dashboards/pharmacist-dashboard.js',
      'dashboard-patient': './assets/js/dashboards/patient-dashboard.js',
      
      // Authentication
      'auth-login': './assets/js/auth/login.js',
      'auth-register': './assets/js/register.js',
      
      // Features
      'appointments-create': './assets/js/appointments-create.js',
      'appointments-calendar': './assets/js/appointments-calendar.js',
      'appointments-edit': './assets/js/appointments-edit.js',
      'appointments-index': './assets/js/appointments-index.js',
      'appointments-view': './assets/js/appointments-view.js',
      'consultations': './assets/js/consultations.js',
      'messages': './assets/js/messages.js',
      'notifications': './assets/js/notifications.js',
      'form-validation': './assets/js/form-validation.js',
      'patients-index': './assets/js/patients-index.js',
      'lab-requests-index': './assets/js/lab-requests-index.js',
      
      // Landing page
      'landing': './assets/js/landing.js',
    },
    
    output: {
      path: path.resolve(__dirname, 'assets/dist'),
      filename: isProduction ? 'js/[name].[contenthash:8].js' : 'js/[name].js',
      chunkFilename: isProduction ? 'js/[name].[contenthash:8].chunk.js' : 'js/[name].chunk.js',
      clean: true,
      publicPath: publicPath
    },
    
    optimization: {
      splitChunks: {
        cacheGroups: {
          // Vendor libraries (node_modules)
          vendor: {
            test: /[\\/]node_modules[\\/]/,
            name: 'vendors',
            chunks: 'all',
            priority: 10,
            reuseExistingChunk: true,
          },
          // Common code shared across modules
          shared: {
            minChunks: 2,
            chunks: 'all',
            name: 'shared',
            priority: 5,
            reuseExistingChunk: true,
            enforce: true,
          }
        }
      },
      minimize: isProduction,
      minimizer: [
        new TerserPlugin({
          terserOptions: {
            compress: {
              drop_console: isProduction,
              drop_debugger: isProduction,
            },
            format: {
              comments: false,
            },
          },
          extractComments: false,
        }),
      ],
      runtimeChunk: 'single', // Extract webpack runtime into separate file
    },
    
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env'],
              cacheDirectory: true,
            }
          }
        },
        {
          test: /\.css$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                sourceMap: !isProduction,
              }
            },
            {
              loader: 'postcss-loader',
              options: {
                sourceMap: !isProduction,
              }
            }
          ]
        }
      ]
    },
    
    plugins: [
      new MiniCssExtractPlugin({
        filename: isProduction ? 'css/[name].[contenthash:8].css' : 'css/[name].css',
        chunkFilename: isProduction ? 'css/[name].[contenthash:8].chunk.css' : 'css/[name].chunk.css',
      }),
      
      // Auto-inject jQuery
      new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery'
      }),
      
      new WebpackManifestPlugin({
        fileName: 'manifest.json',
        publicPath: appPath ? appPath + '/assets/dist/' : 'assets/dist/',
        generate: (seed, files, entrypoints) => {
          const manifestFiles = files.reduce((manifest, file) => {
            manifest[file.name] = file.path;
            return manifest;
          }, seed);
          const entrypointFiles = entrypoints.main ? entrypoints.main.filter(fileName => !fileName.endsWith('.map')) : [];

          return {
            files: manifestFiles,
            entrypoints: entrypointFiles,
          };
        },
      }),
      
      // Production-only plugins
      ...(isProduction ? [
        // PurgeCSS to remove unused CSS
        new PurgeCSSPlugin({
          paths: glob.sync(`${path.join(__dirname, 'includes')}/**/*.php`, { nodir: true }),
          safelist: {
            // Bootstrap dynamic classes
            standard: [/^bs-/, /^modal/, /^dropdown/, /^tooltip/, /^popover/, /^btn/, /^alert/, /^badge/],
            // Third-party library classes
            deep: [/select2/, /datatable/, /calendar/, /fc-/],
            // State classes
            greedy: [/fade/, /show/, /active/, /disabled/, /collapse/]
          }
        }),
        
        // Compress assets with gzip
        new CompressionPlugin({
          algorithm: 'gzip',
          test: /\.(js|css|html|svg)$/,
          threshold: 10240, // Only compress files > 10KB
          minRatio: 0.8,
        }),
      ] : []),
      
      // Bundle analyzer (only when ANALYZE=true)
      ...(shouldAnalyze ? [
        new BundleAnalyzerPlugin({
          analyzerMode: 'static',
          reportFilename: '../bundle-report.html',
          openAnalyzer: false,
        })
      ] : []),
    ],
    
    resolve: {
      extensions: ['.js', '.json'],
      alias: {
        '@': path.resolve(__dirname, 'assets/js'),
        '@common': path.resolve(__dirname, 'assets/js/common'),
        '@shared': path.resolve(__dirname, 'assets/js/shared'),
        '@core': path.resolve(__dirname, 'assets/js/core'),
        '@dashboards': path.resolve(__dirname, 'assets/js/dashboards'),
      }
    },
    
    devtool: isProduction ? 'source-map' : 'eval-source-map',
    
    stats: {
      colors: true,
      modules: false,
      children: false,
      chunks: false,
      chunkModules: false
    },
    
    performance: {
      hints: isProduction ? 'warning' : false,
      maxEntrypointSize: 512000,
      maxAssetSize: 512000,
    }
  };
};
