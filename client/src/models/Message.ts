export default class Message {
  private readonly _text: string;
  private readonly _variant: string;
  constructor(data?: any) {
    console.log('Hallo');
    if (data) {
      this._text = data._text;
      this._variant = data._variant;
    } else {

      this._text = '';
      this._variant = 'danger';
    }
  }

  get text(): string {
    return this._text;
  }

  get variant(): string {
    return this._variant;
  }
}