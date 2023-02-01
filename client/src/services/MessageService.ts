import { ApiService } from "/src/services/index";

export default class MessageService extends ApiService {
  static getMessage() {
    const url = this.getBaseUrl() + 'message';
    const requestOptions = {
      method: 'GET',
      headers: this.getHeaders()
    }
    return fetch(url, requestOptions)
      .then(this.handleResponse);
  }
}