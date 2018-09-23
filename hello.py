# TODO Set up a real prod environment
# See http://flask.pocoo.org/docs/1.0/tutorial/deploy/

from flask import Flask
app = Flask(__name__)

@app.route("/")
def hello():
    return "Hello World!"
